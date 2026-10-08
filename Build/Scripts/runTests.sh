#!/usr/bin/env bash

#
# fgtclb/t3oodle test runner based on docker/podman.
#

# The containers and the network of a run are removed on every way out of this script: its end,
# every "exit" below, and SIGINT, SIGTERM and SIGHUP. A database container is started detached
# ("run -d"), so no client process of it is left to take a signal - with SIGINT trapped alone, a
# run stopped by SIGTERM or SIGHUP (a timeout, a closed terminal) left it running after its
# "functional-*" sibling was gone. SIGKILL cannot be trapped, it is left to the reaper started
# once the network exists.
#
# The trap is installed in CI as well. The core excludes it there for debugging a cancelled job
# on its own runners (review of https://review.typo3.org/c/Packages/TYPO3.CMS/+/85303), a GitHub
# hosted runner is discarded with its job, so nothing is lost here by removing the containers.
#
# The exit code is kept: EXIT only cleans up, the signals exit with 128 + their number - except
# SIGINT, which keeps the 2 it always had. The signals are ignored while the cleanup runs, so a
# second ctrl-c does not abandon it half way. bash runs a trap only once the command in the
# foreground has returned: a signal to the process group - ctrl-c, a closed terminal, a
# supervisor - stops the container as well and is handled at once, a signal to this script alone
# waits for the container to finish.
NETWORK=""
CLEANED_UP=0
trap 'cleanUp' EXIT
trap 'handleSignal INT 2' INT
trap 'handleSignal TERM 143' TERM
trap 'handleSignal HUP 129' HUP

handleSignal() {
    trap '' INT TERM HUP
    echo "runTests.sh SIG${1} signal emitted" >&2
    cleanUp
    exit "${2}"
}

waitForDatabase() {
    # Waits until the database server answers a query, and aborts the whole run
    # when it does not.
    #
    # It asks for a query rather than probing the TCP port, because an open port
    # is not a ready server: the mysql image runs a temporary server while it
    # initialises its data directory.
    #
    # The probe runs the vendor's own client from the database image itself,
    # which is the only client guaranteed to speak the protocol of the version
    # under test and needs no extension compiled into the PHP image. MariaDB
    # renamed that client, so both names are tried - "mysql" is a deprecated
    # symlink in current MariaDB and absent from future ones, while "mariadb"
    # does not exist in the older images this repository still supports.
    #
    # 60 rather than 10 seconds: mysql:8.0 needs 12-13s under docker to
    # initialise a fresh data directory, about twice as long as under podman,
    # so an 11 second budget aborted the functional mysql suites at random.
    local KIND=${1}
    local HOST=${2}
    local IMAGE=${3}
    local PROBE=""
    case ${KIND} in
        mariadb|mysql)
            # MYSQL_PWD rather than "-p", which warns about the password on the
            # command line once per probe iteration.
            PROBE="MYSQL_PWD=funcp sh -c 'mysql -h ${HOST} -u root -e \"SELECT 1\" || mariadb -h ${HOST} -u root -e \"SELECT 1\"' >/dev/null 2>&1"
            ;;
        postgres)
            PROBE="PGPASSWORD=funcp psql -h ${HOST} -U funcu -d funcu -c 'SELECT 1' >/dev/null 2>&1"
            ;;
        *)
            echo "waitForDatabase() does not know the DBMS \"${KIND}\"." >&2
            cleanUp
            exit 1
            ;;
    esac
    local TESTCOMMAND="
        COUNT=0;
        until ${PROBE}; do
            if [ \"\${COUNT}\" -gt 60 ]; then
              echo \"The ${KIND} server \\\"${HOST}\\\" did not answer a query within 60 seconds. Aborting.\";
              exit 1;
            fi;
            sleep 1;
            COUNT=\$((COUNT + 1));
        done;
    "
    ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name wait-for-${SUFFIX} ${IMAGE} /bin/sh -c "${TESTCOMMAND}"
    if [[ $? -gt 0 ]]; then
        # Not "kill -SIGINT -$$": that signals the process group "$$" leads,
        # and this script leads one only when an interactive shell started
        # it. Started by a CI step it is a plain child of the step's shell,
        # the kill failed with "No such process", the run carried on and the
        # test suite connected to a database that was not listening.
        cleanUp
        exit 1
    fi
}

cleanUp() {
    # Removes every container attached to the network of this run, in whatever state - "-a", so
    # one that was created but never started, or has stopped, goes as well - and then the network.
    # Runs more than once on most paths - at the end of the script, and the EXIT trap after it - and
    # on the early exits before the network exists, so it does its work once and only once there is
    # a network.
    if [[ ${CLEANED_UP} -eq 1 ]] || [[ -z "${NETWORK}" ]] || [[ -z "${CONTAINER_BIN}" ]]; then
        return 0
    fi
    ATTACHED_CONTAINERS=$(${CONTAINER_BIN} ps -a --filter network=${NETWORK} --format='{{.Names}}')
    for ATTACHED_CONTAINER in ${ATTACHED_CONTAINERS}; do
        ${CONTAINER_BIN} rm -f ${ATTACHED_CONTAINER} >/dev/null
    done
    ${CONTAINER_BIN} network rm -f ${NETWORK} >/dev/null
    CLEANED_UP=1
}

ensureImages() {
    # Makes sure every image given is present, pulling one that is not with a bounded retry, and
    # ends the run naming the image when that does not succeed.
    #
    # "run" pulls a missing image by itself, once, and gives up: a registry that did not answer in
    # time ("context deadline exceeded", exit code 125 of "docker run") fails a CI job before its
    # suite started, and a rerun of the same job passes. An image that is present is not pulled
    # again, so a local run needs no registry once its images are there, "-u" updates them.
    local IMAGE
    local ATTEMPT
    for IMAGE in "$@"; do
        if ${CONTAINER_BIN} image inspect "${IMAGE}" >/dev/null 2>&1; then
            continue
        fi
        for ATTEMPT in 1 2 3; do
            if ${CONTAINER_BIN} pull "${IMAGE}" >&2; then
                continue 2
            fi
            if [[ ${ATTEMPT} -lt 3 ]]; then
                echo "Pulling \"${IMAGE}\" failed, attempt ${ATTEMPT} of 3. Retrying in $((ATTEMPT * 10)) seconds." >&2
                sleep $((ATTEMPT * 10))
            fi
        done
        echo "The image \"${IMAGE}\" is not present and could not be pulled in 3 attempts. Nothing was run." >&2
        exit 1
    done
}

handleDbmsOptions() {
    # -a, -d, -i depend on each other. Validate input combinations and set defaults.
    case ${DBMS} in
        mariadb)
            [ -z "${DATABASE_DRIVER}" ] && DATABASE_DRIVER="mysqli"
            if [ "${DATABASE_DRIVER}" != "mysqli" ] && [ "${DATABASE_DRIVER}" != "pdo_mysql" ]; then
                echo "Invalid combination -d ${DBMS} -a ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            [ -z "${DBMS_VERSION}" ] && DBMS_VERSION="10.4"
            if ! [[ ${DBMS_VERSION} =~ ^(10.4|10.5|10.6|10.7|10.8|10.9|10.10|10.11|11.0|11.1)$ ]]; then
                echo "Invalid combination -d ${DBMS} -i ${DBMS_VERSION}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            ;;
        mysql)
            [ -z "${DATABASE_DRIVER}" ] && DATABASE_DRIVER="mysqli"
            if [ "${DATABASE_DRIVER}" != "mysqli" ] && [ "${DATABASE_DRIVER}" != "pdo_mysql" ]; then
                echo "Invalid combination -d ${DBMS} -a ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            [ -z "${DBMS_VERSION}" ] && DBMS_VERSION="8.0"
            if ! [[ ${DBMS_VERSION} =~ ^(8.0|8.1|8.2|8.3|8.4)$ ]]; then
                echo "Invalid combination -d ${DBMS} -i ${DBMS_VERSION}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            ;;
        postgres)
            if [ -n "${DATABASE_DRIVER}" ]; then
                echo "Invalid combination -d ${DBMS} -a ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            [ -z "${DBMS_VERSION}" ] && DBMS_VERSION="10"
            if ! [[ ${DBMS_VERSION} =~ ^(10|11|12|13|14|15|16)$ ]]; then
                echo "Invalid combination -d ${DBMS} -i ${DBMS_VERSION}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            ;;
        sqlite)
            if [ -n "${DATABASE_DRIVER}" ]; then
                echo "Invalid combination -d ${DBMS} -a ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            if [ -n "${DBMS_VERSION}" ]; then
                echo "Invalid combination -d ${DBMS} -i ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            ;;
        *)
            echo "Invalid option -d ${DBMS}" >&2
            echo >&2
            echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
            exit 1
            ;;
    esac
}

cleanCacheFiles() {
    echo -n "Clean caches ... "
    rm -rf \
        .Build/.cache \
        .php-cs-fixer.cache
    echo "done"
}

cleanTestFiles() {
    # test related
    echo -n "Clean test related files ... "
    rm -rf \
        .Build/public/typo3temp/var/tests/
    echo "done"
}

cleanRenderedDocumentationFiles() {
    echo -n "Clean rendered documentation files ... "
    rm -rf \
        Documentation-GENERATED-temp
    echo "done"
}

loadHelp() {
    # Load help text into $HELP
    read -r -d '' HELP <<EOF
TYPO3 core test runner. Execute acceptance, unit, functional and other test suites in
a container based test environment. Handles execution of single test files, sending
xdebug information to a local IDE and more.

Usage: $0 [options] [file]

Options:
    -s <...>
        Specifies which test suite to run
            - cgl: cgl test and fix all php files
            - checkBom: check UTF-8 files do not contain BOM
            - checkExceptionCodes: Check for duplicate exception codes
            - checkRst: test .rst files for integrity
            - checkTestMethodsPrefix: check tests methods do not start with "test"
            - clean: clean up build, cache and testing related files and folders
            - cleanCache: clean up cache related files and folders
            - cleanRenderedDocumentation: clean up rendered documentation files and folders (Documentation-GENERATED-temp)
            - clean: clean up build and testing related files
            - composer: "composer" with all remaining arguments dispatched.
            - composerUpdate: "composer update", handy if host has no PHP
            - functional: functional tests
            - lintPhp: PHP linting
            - lintTypoScript: TypoScript linting
            - renderDocumentation: This uses the official rendering container to render the extension documentation.
            - phpstan: phpstan analyze
            - phpstanGenerateBaseline: regenerate phpstan baseline, handy after phpstan updates
            - unit: PHP unit tests

    -b <docker|podman>
        Container environment:
            - docker
            - podman

        If not specified, podman will be used if available. Otherwise, docker is used.

    -a <mysqli|pdo_mysql>
        Only with -s functional|functionalDeprecated
        Specifies to use another driver, following combinations are available:
            - mysql
                - mysqli (default)
                - pdo_mysql
            - mariadb
                - mysqli (default)
                - pdo_mysql

    -d <sqlite|mariadb|mysql|postgres>
        Only with -s functional|functionalDeprecated|acceptance|acceptanceInstall
        Specifies on which DBMS tests are performed
            - sqlite: (default): use sqlite
            - mariadb: use mariadb
            - mysql: use MySQL
            - postgres: use postgres

    -i version
        Specify a specific database version
        With "-d mariadb":
            - 10.4   short-term, maintained until 2024-06-18 (default)
            - 10.5   short-term, maintained until 2025-06-24
            - 10.6   long-term, maintained until 2026-06
            - 10.7   short-term, no longer maintained
            - 10.8   short-term, maintained until 2023-05
            - 10.9   short-term, maintained until 2023-08
            - 10.10  short-term, maintained until 2023-11
            - 10.11  long-term, maintained until 2028-02
            - 11.0   development series
            - 11.1   short-term development series
        With "-d mysql":
            - 8.0   maintained until 2026-04 (default)
            - 8.1   unmaintained since 2023-10
            - 8.2   unmaintained since 2024-01
            - 8.3   maintained until 2024-04
            - 8.4   maintained until 2032-04 LTS
        With "-d postgres":
            - 10    unmaintained since 2022-11-10 (default)
            - 11    maintained until 2023-11-09
            - 12    maintained until 2024-11-14
            - 13    maintained until 2025-11-13
            - 14    maintained until 2026-11-12
            - 15    maintained until 2027-11-11
            - 16    maintained until 2028-11-09

    -t <11|12>
        Only with -s composerInstall|composerInstallMin|composerInstallMax
        Specifies the TYPO3 CORE Version to be used
            - 11: (default) use TYPO3 v11
            - 12: use TYPO3 v12

    -p <8.1|8.2|8.3|8.4|8.5>
        Specifies the PHP minor version to be used
            - 8.1: (default) use PHP 8.1
            - 8.2: use PHP 8.2
            - 8.3: use PHP 8.3
            - 8.4: use PHP 8.4
            - 8.5: use PHP 8.5

    -x
        Only with -s functional|functionalDeprecated|unit|unitDeprecated|unitRandom|acceptance|acceptanceInstall
        Send information to host instance for test or system under test break points. This is especially
        useful if a local PhpStorm instance is listening on default xdebug port 9003. A different port
        can be selected with -y

    -y <port>
        Send xdebug information to a different port than default 9003 if an IDE like PhpStorm
        is not listening on default port.

    -o <number>
        Only with -s unitRandom
        Set specific random seed to replay a random run in this order again. The phpunit randomizer
        outputs the used seed at the end (in gitlab core testing logs, too). Use that number to
        replay the unit tests in that order.

    -n
        Only with -s cgl
        Activate dry-run in CGL check that does not actively change files and only prints broken ones.

    -u
        Update existing typo3/core-testing-*:latest container images and remove dangling local volumes.
        New images are published once in a while and only the latest ones are supported by core testing.
        Use this if weird test errors occur. Also removes obsolete image versions of typo3/core-testing-*.

    -h
        Show this help.

Examples:
    # Run all core unit tests using PHP 8.1
    ./Build/Scripts/runTests.sh
    ./Build/Scripts/runTests.sh -s unit

    # Run all core units tests and enable xdebug (have a PhpStorm listening on port 9003!)
    ./Build/Scripts/runTests.sh -x

    # Run unit tests in phpunit verbose mode with xdebug on PHP 8.1 and filter for test canRetrieveValueWithGP
    ./Build/Scripts/runTests.sh -x -p 8.1 -e "-v --filter canRetrieveValueWithGP"

    # Run functional tests in phpunit with a filtered test method name in a specified file
    # example will currently execute two tests, both of which start with the search term
    ./Build/Scripts/runTests.sh -s functional -e "--filter deleteContent" typo3/sysext/core/Tests/Functional/DataHandling/Regular/Modify/ActionTest.php

    # Run functional tests on postgres with xdebug, php 8.1 and execute a restricted set of tests
    ./Build/Scripts/runTests.sh -x -p 8.1 -s functional -d postgres typo3/sysext/core/Tests/Functional/Authentication

    # Run functional tests on postgres 11
    ./Build/Scripts/runTests.sh -s functional -d postgres -k 11

    # Run restricted set of application acceptance tests
    ./Build/Scripts/runTests.sh -s acceptance typo3/sysext/core/Tests/Acceptance/Application/Login/BackendLoginCest.php:loginButtonMouseOver

    # Run installer tests of a new instance on sqlite
    ./Build/Scripts/runTests.sh -s acceptanceInstall -d sqlite
EOF
}

# Test if docker exists, else exit out with error
if ! type "docker" >/dev/null 2>&1 && ! type "podman" >/dev/null 2>&1; then
    echo "This script relies on docker or podman. Please install" >&2
    exit 1
fi

# Option defaults
TEST_SUITE="unit"
CORE_VERSION="12"
DBMS="sqlite"
PHP_VERSION="8.1"
PHP_XDEBUG_ON=0
PHP_XDEBUG_PORT=9003
PHPUNIT_RANDOM=""
CGLCHECK_DRY_RUN=0
DATABASE_DRIVER=""
DBMS_VERSION=""
CONTAINER_BIN=""
CONTAINER_HOST="host.docker.internal"

# Option parsing updates above default vars
# Reset in case getopts has been used previously in the shell
OPTIND=1
# Array for invalid options
INVALID_OPTIONS=()
# Simple option parsing based on getopts (! not getopt)
while getopts "a:b:s:d:i:p:t:xy:o:nhu" OPT; do
    case ${OPT} in
        s)
            TEST_SUITE=${OPTARG}
            ;;
        b)
            if ! [[ ${OPTARG} =~ ^(docker|podman)$ ]]; then
                INVALID_OPTIONS+=("${OPTARG}")
            fi
            CONTAINER_BIN=${OPTARG}
            ;;
        a)
            DATABASE_DRIVER=${OPTARG}
            ;;
        d)
            DBMS=${OPTARG}
            ;;
        i)
            DBMS_VERSION=${OPTARG}
            ;;
        p)
            PHP_VERSION=${OPTARG}
            if ! [[ ${PHP_VERSION} =~ ^(8.1|8.2|8.3|8.4|8.5)$ ]]; then
                INVALID_OPTIONS+=("p ${OPTARG}")
            fi
            ;;
        t)
            CORE_VERSION=${OPTARG}
            if ! [[ ${CORE_VERSION} =~ ^(11|12)$ ]]; then
                INVALID_OPTIONS+=("t ${OPTARG}")
            fi
            ;;
        x)
            PHP_XDEBUG_ON=1
            ;;
        y)
            PHP_XDEBUG_PORT=${OPTARG}
            ;;
        o)
            PHPUNIT_RANDOM="--random-order-seed=${OPTARG}"
            ;;
        n)
            CGLCHECK_DRY_RUN=1
            ;;
        h)
            loadHelp
            echo "${HELP}"
            exit 0
            ;;
        u)
            TEST_SUITE=update
            ;;
        \?)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
        :)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
    esac
done

# Exit on invalid options
if [ ${#INVALID_OPTIONS[@]} -ne 0 ]; then
    echo "Invalid option(s):" >&2
    for I in "${INVALID_OPTIONS[@]}"; do
        echo "-"${I} >&2
    done
    echo >&2
    echo "call \".Build/Scripts/runTests.sh -h\" to display help and valid options"
    exit 1
fi

handleDbmsOptions

COMPOSER_ROOT_VERSION="2.0.6-dev"
CONTAINER_INTERACTIVE="-it --init"
HOST_UID=$(id -u)
HOST_GID=$(id -g)
# Additional container parameters, provided by the environment. Empty unless the caller
# exports it, which is how the portfolio harnesses inject CI specific flags.
CI_PARAMS="${CI_PARAMS:-}"
USERSET=""
if [ $(uname) != "Darwin" ]; then
    USERSET="--user $HOST_UID"
fi

# Go to the directory this script is located, so everything else is relative
# to this dir, no matter from where this script is called, then go up two dirs.
THIS_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
cd "$THIS_SCRIPT_DIR" || exit 1
cd ../../ || exit 1
ROOT_DIR="${PWD}"

# Create .cache dir: composer need this.
mkdir -p .Build/.cache
mkdir -p .Build/public/typo3temp/var/tests


IS_CORE_CI=0
# ENV var "CI" is set by gitlab-ci. We use it here to distinct 'local' and 'CI' environment.
if [ "${CI}" == "true" ]; then
    IS_CORE_CI=1
    IMAGE_PREFIX=""
    CONTAINER_INTERACTIVE=""
elif [ ! -t 0 ] || [ ! -t 1 ]; then
    # If stdin or stdout is not a TTY (e.g. a script runner, pipe, or non-interactive shell),
    # drop the interactive "-it" flags automatically to avoid podman warning "The input device
    # is not a TTY." and docker failure, and to keep redirected output free of TTY control characters.
    # Keep "--init" so the PID 1 init process still forwards signals (e.g. ctrl-c) to the test process.
    #
    # It also stops a scripted run from hanging forever: with a pseudo TTY every tool inside the
    # container believes it may ask a question, and composer does - it asks whether a plugin missing
    # from "allow-plugins" is trusted, and then waits for an answer nobody is there to give.
    CONTAINER_INTERACTIVE="--init"
fi

# determine default container binary to use: 1. podman 2. docker
if [[ -z "${CONTAINER_BIN}" ]]; then
    if type "podman" >/dev/null 2>&1; then
        CONTAINER_BIN="podman"
    elif type "docker" >/dev/null 2>&1; then
        CONTAINER_BIN="docker"
    fi
fi

IMAGE_PHP="ghcr.io/typo3/core-testing-$(echo "php${PHP_VERSION}" | sed -e 's/\.//'):latest"
IMAGE_DOCS="ghcr.io/typo3-documentation/render-guides:latest"
IMAGE_SELENIUM="docker.io/selenium/standalone-chrome:4.0.0-20211102"
IMAGE_MARIADB="docker.io/mariadb:${DBMS_VERSION}"
IMAGE_MYSQL="docker.io/mysql:${DBMS_VERSION}"
IMAGE_POSTGRES="docker.io/postgres:${DBMS_VERSION}-alpine"


# Detect arm64 and use a seleniarm image.
# In a perfect world selenium would have a arm64 integrated, but that is not on the horizon.
# So for the time being we have to use seleniarm image.
ARCH=$(uname -m)
if [ ${ARCH} = "arm64" ]; then
    IMAGE_SELENIUM="docker.io/seleniarm/standalone-chromium:4.1.2-20220227"
fi
echo "Architecture" ${ARCH} "requires" ${IMAGE_SELENIUM} "to run acceptance tests."

# Set $1 to first mass argument, this is the optional test file or test directory to execute
shift $((OPTIND - 1))

SUFFIX=$(echo $RANDOM)
NETWORK="fgtclb-t3oodle-${SUFFIX}"
# A network of that name exists when the suffix collides with a run still going on. Joining it
# would put this run's containers beside that run's, and the cleanup of either would remove the
# containers of both. NETWORK is cleared first, so the cleanup on exit leaves that network alone.
${CONTAINER_BIN} network create ${NETWORK} >/dev/null || {
    echo "The container network \"${NETWORK}\" could not be created, it may belong to another run. Nothing was run." >&2
    NETWORK=""
    exit 1
}

if [ "${CONTAINER_BIN}" == "docker" ]; then
    # docker needs the add-host for xdebug remote debugging. podman has host.container.internal built in
    CONTAINER_COMMON_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm --network ${NETWORK} --add-host ${CONTAINER_HOST}:host-gateway ${USERSET} -v ${ROOT_DIR}:${ROOT_DIR} -w ${ROOT_DIR}"
    CONTAINER_SIMPLE_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm --network ${NETWORK} --add-host ${CONTAINER_HOST}:host-gateway ${USERSET} -v ${ROOT_DIR}:${ROOT_DIR} -w ${ROOT_DIR}"
    DOCUMENTATION_COMMON_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm ${USERSET} -v ${ROOT_DIR}:/project"
    # docker creates a tmpfs owned by "root:root", inheriting the mode of its host
    # mountpoint, while "${USERSET}" above passes a uid but no group and therefore runs
    # the container as "uid=${HOST_UID} gid=0". At a CI umask of 0022 the mountpoint is
    # 0755, so group 0 gets "r-x" and no test database can be created.
    #
    # "uid"/"gid" address that at the source: the mount is owned by the user the container
    # runs as, whatever the umask of the host mountpoint. "mode=1777" is the workaround the
    # docker adoption introduced instead, and is kept next to them - it is what has been
    # proven on a GitHub hosted runner, and it costs nothing to leave in place.
    #
    # None of this reproduces at the 0002 umask of a typical workstation, where the
    # mountpoint comes up 0775 and the group bit already grants access. Use "umask 0022".
    TMPFS_MOUNT_OPTIONS="rw,noexec,nosuid,uid=${HOST_UID},gid=${HOST_GID},mode=1777"
else
    # podman
    CONTAINER_HOST="host.containers.internal"
    # Rootless podman maps the container root to the host user, so the tmpfs is writable
    # without an explicit owner. "mode=1777" is kept for the rootful case.
    TMPFS_MOUNT_OPTIONS="rw,noexec,nosuid,mode=1777"
    CONTAINER_COMMON_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm --network ${NETWORK} -v ${ROOT_DIR}:${ROOT_DIR} -w ${ROOT_DIR}"
    CONTAINER_SIMPLE_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm -v ${ROOT_DIR}:${ROOT_DIR} -w ${ROOT_DIR}"
    DOCUMENTATION_COMMON_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm -v ${ROOT_DIR}:${ROOT_DIR} -v ${ROOT_DIR}:/project"
fi

# The traps at the top do not see SIGKILL, and a SIGTERM followed by SIGKILL can end this script
# before its trap is through: a supervisor may send SIGKILL shortly after SIGTERM, as "timeout -k"
# does, and the GitHub runner ends a step it cancels with SIGINT, SIGTERM and then SIGKILL. So a
# reaper waits for this script to end and removes what is left of the run. After a run that
# removed everything itself it finds nothing. It looks once a second and removes the containers
# in one call. It runs in a session of its own, out of reach of any signal to the process group
# of this script. Without "setsid" (macOS) it shares that process group, ignoring the signals
# that end a run, and a SIGKILL to the group ends it along with the run.
REAPER_SESSION=""
type setsid >/dev/null 2>&1 && REAPER_SESSION="setsid"
${REAPER_SESSION} /bin/sh -c '
    trap "" INT HUP TERM
    while kill -0 "$1" 2>/dev/null; do sleep 1; done
    CONTAINERS=$("$2" ps -a --filter "network=$3" --format "{{.Names}}")
    [ -n "${CONTAINERS}" ] && "$2" rm -f ${CONTAINERS}
    "$2" network rm -f "$3"
' reaper "$$" "${CONTAINER_BIN}" "${NETWORK}" </dev/null >/dev/null 2>&1 &

if [ ${PHP_XDEBUG_ON} -eq 0 ]; then
    XDEBUG_MODE="-e XDEBUG_MODE=off"
    XDEBUG_CONFIG=" "
else
    XDEBUG_MODE="-e XDEBUG_MODE=debug -e XDEBUG_TRIGGER=foo"
    XDEBUG_CONFIG="client_port=${PHP_XDEBUG_PORT} client_host=${CONTAINER_HOST}"
fi

# The images of the suite, before its first "run", see "ensureImages()". A suite missing here
# still works: "run" pulls its image, only without the retry. "renderDocumentation" is missing on
# purpose, it runs with "--pull always".
case ${TEST_SUITE} in
    functional)
        case ${DBMS} in
            mariadb) ensureImages "${IMAGE_PHP}" "${IMAGE_MARIADB}" ;;
            mysql) ensureImages "${IMAGE_PHP}" "${IMAGE_MYSQL}" ;;
            postgres) ensureImages "${IMAGE_PHP}" "${IMAGE_POSTGRES}" ;;
            *) ensureImages "${IMAGE_PHP}" ;;
        esac
        ;;
    cgl|checkBom|checkExceptionCodes|checkRst|checkTestMethodsPrefix|composer|composerUpdate|lintPhp|lintTypoScript|phpstan|phpstanGenerateBaseline|unit|unitRandom)
        ensureImages "${IMAGE_PHP}"
        ;;
esac

# Suite execution
case ${TEST_SUITE} in
    cgl)
        if [ "${CGLCHECK_DRY_RUN}" -eq 1 ]; then
            COMMAND="php -dxdebug.mode=off .Build/bin/php-cs-fixer fix --config Build/php-cs-fixer/php-cs-rules.php -v --dry-run --using-cache no --diff"
        else
            COMMAND="php -dxdebug.mode=off .Build/bin/php-cs-fixer fix --config Build/php-cs-fixer/php-cs-rules.php --using-cache no"
        fi
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name cgl-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    checkBom)
        COMMAND="Build/Scripts/checkUtf8Bom.sh"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name cgl-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    checkRst)
        COMMAND="php -dxdebug.mode=off Build/Scripts/validateRstFiles.php"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name cgl-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    checkExceptionCodes)
        COMMAND="Build/Scripts/duplicateExceptionCodeCheck.sh"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name cgl-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    checkTestMethodsPrefix)
        COMMAND="php -dxdebug.mode=off Build/Scripts/testMethodPrefixChecker.php"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name cgl-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    clean)
        cleanCacheFiles
        cleanRenderedDocumentationFiles
        cleanTestFiles
        ;;
    cleanCache)
        cleanCacheFiles
        ;;
    cleanRenderedDocumentation)
        cleanRenderedDocumentationFiles
        ;;
    cleanTests)
        cleanTestFiles
        ;;
    composer)
        COMMAND=(composer "$@")
        ${CONTAINER_BIN} run ${CONTAINER_SIMPLE_PARAMS} --name composer-command-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    composerUpdate)
        # backup current composer.json
        cp -Rf composer.json composer.json.orig
        # The vendor tree must go, not just the lock file: Composer boots plugins from
        # it before resolving, so a leftover tree from another "-t" runs the previous
        # core's "typo3/class-alias-loader" over the newly installed one. ".Build/.cache"
        # is kept so the reinstall is served from the local Composer cache.
        rm -rf .Build/vendor .Build/bin composer.lock
        ${CONTAINER_BIN} run ${CONTAINER_SIMPLE_PARAMS} --name composer-update-${CORE_VERSION}-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} composer require --dev "typo3/minimal":"^${CORE_VERSION}"
        SUITE_EXIT_CODE=$?
        # restore composer json
        cp -Rf composer.json.orig composer.json
        ;;
    functional)
        PHPUNIT_CONFIG_FILE="Build/phpunit/FunctionalTests.xml"
        COMMAND=(.Build/bin/phpunit -c ${PHPUNIT_CONFIG_FILE} --exclude-group not-${DBMS} "$@")
        # Server options of the MySQL and MariaDB containers. Their data directory is a tmpfs,
        # thrown away with the container, so durability buys nothing: no binary log (on by
        # default in MySQL 8, off in MariaDB anyway), the redo log written but not flushed on
        # every commit, no doublewrite buffer. Taken from "sbuerk/theme-extension-development",
        # where every MySQL and MariaDB version "-i" accepts and whose image was at hand came
        # up with them.
        MYSQL_SERVER_OPTIONS="--skip-log-bin --innodb-flush-log-at-trx-commit=2 --innodb-doublewrite=0"
        case ${DBMS} in
            mariadb)
                echo "Using driver: ${DATABASE_DRIVER}"
                ${CONTAINER_BIN} run --name mariadb-func-${SUFFIX} --network ${NETWORK} -d -e MYSQL_ROOT_PASSWORD=funcp --tmpfs /var/lib/mysql/:rw,noexec,nosuid ${IMAGE_MARIADB} ${MYSQL_SERVER_OPTIONS} >/dev/null
                waitForDatabase mariadb mariadb-func-${SUFFIX} ${IMAGE_MARIADB}
                CONTAINERPARAMS="-e typo3DatabaseDriver=${DATABASE_DRIVER} -e typo3DatabaseName=func_test -e typo3DatabaseUsername=root -e typo3DatabaseHost=mariadb-func-${SUFFIX} -e typo3DatabasePassword=funcp"
                ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name functional-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${CONTAINERPARAMS} ${IMAGE_PHP} "${COMMAND[@]}"
                SUITE_EXIT_CODE=$?
                ;;
            mysql)
                echo "Using driver: ${DATABASE_DRIVER}"
                ${CONTAINER_BIN} run --name mysql-func-${SUFFIX} --network ${NETWORK} -d -e MYSQL_ROOT_PASSWORD=funcp --tmpfs /var/lib/mysql/:rw,noexec,nosuid ${IMAGE_MYSQL} ${MYSQL_SERVER_OPTIONS} >/dev/null
                waitForDatabase mysql mysql-func-${SUFFIX} ${IMAGE_MYSQL}
                CONTAINERPARAMS="-e typo3DatabaseDriver=${DATABASE_DRIVER} -e typo3DatabaseName=func_test -e typo3DatabaseUsername=root -e typo3DatabaseHost=mysql-func-${SUFFIX} -e typo3DatabasePassword=funcp"
                ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name functional-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${CONTAINERPARAMS} ${IMAGE_PHP} "${COMMAND[@]}"
                SUITE_EXIT_CODE=$?
                ;;
            postgres)
                ${CONTAINER_BIN} run --name postgres-func-${SUFFIX} --network ${NETWORK} -d -e POSTGRES_PASSWORD=funcp -e POSTGRES_USER=funcu --tmpfs /var/lib/postgresql/data:rw,noexec,nosuid ${IMAGE_POSTGRES} >/dev/null
                waitForDatabase postgres postgres-func-${SUFFIX} ${IMAGE_POSTGRES}
                CONTAINERPARAMS="-e typo3DatabaseDriver=pdo_pgsql -e typo3DatabaseName=bamboo -e typo3DatabaseUsername=funcu -e typo3DatabaseHost=postgres-func-${SUFFIX} -e typo3DatabasePassword=funcp"
                ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name functional-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${CONTAINERPARAMS} ${IMAGE_PHP} "${COMMAND[@]}"
                SUITE_EXIT_CODE=$?
                ;;
            sqlite)
                # create sqlite tmpfs mount typo3temp/var/tests/functional-sqlite-dbs/ to avoid permission issues
                mkdir -p "${ROOT_DIR}/.Build/public/typo3temp/var/tests/functional-sqlite-dbs/"
                # "${TMPFS_MOUNT_OPTIONS}" carries the owner and mode the mount needs, which
                # differ per container binary - see where it is assigned. Without them the
                # test databases cannot be created and every test fails with "unable to open
                # database file".
                CONTAINERPARAMS="-e typo3DatabaseDriver=pdo_sqlite --tmpfs ${ROOT_DIR}/.Build/public/typo3temp/var/tests/functional-sqlite-dbs/:${TMPFS_MOUNT_OPTIONS}"
                ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name functional-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${CONTAINERPARAMS} ${IMAGE_PHP} "${COMMAND[@]}"
                SUITE_EXIT_CODE=$?
                ;;
        esac
        ;;
    lintPhp)
        COMMAND="find . -name \\*.php ! -path "./.Build/\\*" ! -path "./.cache/\\*" -print0 | xargs -0 -n1 -P4 php -dxdebug.mode=off -l >/dev/null"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name lint-php-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    lintTypoScript)
        COMMAND="php -dxdebug.mode=off .Build/bin/typoscript-lint --ansi --fail-on-warnings --config=./Build/typoscript-lint/typoscript-lint.yml"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name lint-php-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    renderDocumentation)
        ${CONTAINER_BIN} run ${DOCUMENTATION_COMMON_PARAMS} --name rendering-documentation-${SUFFIX} --pull always -w /project ${IMAGE_DOCS} --config=Documentation
        SUITE_EXIT_CODE=$?
        ;;
    phpstan)
        PHPSTAN_CONFIG_FILE="Build/phpstan/Core${CORE_VERSION}/phpstan.neon"
        COMMAND=(php -dxdebug.mode=off .Build/bin/phpstan analyse -c ${PHPSTAN_CONFIG_FILE} --no-progress --no-interaction --memory-limit 4G "$@")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name phpstan-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    phpstanGenerateBaseline)
        PHPSTAN_CONFIG_FILE="Build/phpstan/Core${CORE_VERSION}/phpstan.neon"
        COMMAND=(php -dxdebug.mode=off .Build/bin/phpstan analyse -c ${PHPSTAN_CONFIG_FILE} --no-progress --no-interaction --memory-limit 4G --allow-empty-baseline --generate-baseline=Build/phpstan/Core${CORE_VERSION}/phpstan-baseline.neon)
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name phpstan-baseline-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    unit)
        PHPUNIT_CONFIG_FILE="Build/phpunit/UnitTests.xml"
        COMMAND=(.Build/bin/phpunit -c ${PHPUNIT_CONFIG_FILE} "$@")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name unit-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    unitRandom)
        PHPUNIT_CONFIG_FILE="Build/phpunit/UnitTests.xml"
        COMMAND=(.Build/bin/phpunit -c ${PHPUNIT_CONFIG_FILE} --order-by=random ${PHPUNIT_RANDOM} "$@")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name unit-random-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    update)
        # pull typo3/core-testing-* versions of those ones that exist locally
        echo "> pull ghcr.io/typo3/core-testing-* versions of those ones that exist locally"
        ${CONTAINER_BIN} images "ghcr.io/typo3/core-testing-*" --format "{{.Repository}}:{{.Tag}}" | xargs -I {} ${CONTAINER_BIN} pull {}
        echo ""
        # remove "dangling" typo3/core-testing-* images (those tagged as <none>)
        echo "> remove \"dangling\" ghcr.io/typo3/core-testing-* images (those tagged as <none>)"
        ${CONTAINER_BIN} images --filter "reference=ghcr.io/typo3/core-testing-*" --filter "dangling=true" --format "{{.ID}}" | xargs -I {} ${CONTAINER_BIN} rmi -f {}
        echo ""
        # pull ghcr.io/web-vision/ versions of those ones that exist locally
        echo "> pull ghcr.io/web-vision/* versions of those ones that exist locally"
        ${CONTAINER_BIN} images "ghcr.io/web-vision/*" --format "{{.Repository}}:{{.Tag}}" | xargs -I {} ${CONTAINER_BIN} pull {}
        echo ""
        # remove "dangling" ghcr.io/web-vision/ images (those tagged as <none>)
        echo "> remove \"dangling\" ghcr.io/web-vision/* images (those tagged as <none>)"
        ${CONTAINER_BIN} images --filter "reference=ghcr.io/web-vision/*" --filter "dangling=true" --format "{{.ID}}" | xargs -I {} ${CONTAINER_BIN} rmi -f {}
        echo ""
        ;;
    *)
        loadHelp
        echo "Invalid -s option argument ${TEST_SUITE}" >&2
        echo >&2
        echo "${HELP}" >&2
        exit 1
        ;;
esac

cleanUp

# Print summary
echo "" >&2
echo "###########################################################################" >&2
echo "Result of ${TEST_SUITE}" >&2
echo "Container runtime: ${CONTAINER_BIN}" >&2
echo "Container suffix: ${SUFFIX}"
if [[ ${IS_CORE_CI} -eq 1 ]]; then
    echo "Environment: CI" >&2
else
    echo "Environment: local" >&2
fi
echo "PHP: ${PHP_VERSION}" >&2
echo "TYPO3: ${CORE_VERSION}" >&2
if [[ ${TEST_SUITE} =~ ^(functional|functionalDeprecated|acceptance|acceptanceInstall)$ ]]; then
    case "${DBMS}" in
        mariadb|mysql|postgres)
            echo "DBMS: ${DBMS}  version ${DBMS_VERSION}  driver ${DATABASE_DRIVER}" >&2
            ;;
        sqlite)
            echo "DBMS: ${DBMS}" >&2
            ;;
    esac
fi
if [[ ${SUITE_EXIT_CODE} -eq 0 ]]; then
    echo "SUCCESS" >&2
else
    echo "FAILURE" >&2
fi
echo "###########################################################################" >&2
echo "" >&2

# Exit with code of test suite - This script return non-zero if the executed test failed.
exit $SUITE_EXIT_CODE
