# TYPO3 CMS Extension: t3oodle

Simple poll extension for TYPO3 CMS. t3oodle allows your frontend users
to create new polls and vote for existing ones.

This extension has been brought to you by **FGTCLB** and has been supported by
**Friedrich-Ebert-Stiftung e. V.**

## Documentation

This extension provides a ReST documentation, located in ``Documentation/``
directory.

You can see a rendered version on https://docs.typo3.org/p/fgtclb/t3oodle once
the extension has been released.

## Demo

You will find a demonstration of the extension on https://t3oodle.com

## Compatibility

| Branch | Version | TYPO3 | PHP                 |
|--------|---------|-------|---------------------|
| master | 2.x-dev | v12   | 8.1, 8.2, 8.3, 8.4  |
| 1      | 1.x-dev | v11   | 8.1, 8.2, 8.3, 8.4  |

## Installation

Install with your flavour:

* Extension Manager
* composer

We prefer composer installation:

```bash
composer require -W 'fgtclb/t3oodle':'^1.0'
```

## Create a release (maintainers only)

Prerequisites:

* git binary
* ssh key allowed to push new branches to the repository
* GitHub command line tool `gh` installed and configured with user having permission to create pull requests.

**Create release**

> Set `RELEASE_BRANCH` to branch release should happen, for example: 'main'.
> Set `RELEASE_VERSION` to release version working on, for example: '5.0.0'.

> [!IMPORTANT]
> Requires `GitHub cli tool` with personal token and
> maintainer permission on the extension repository.

```bash
echo '>> Create release' ; \
  RELEASE_BRANCH='master' ; \
  RELEASE_VERSION='1.0.1' ; \
  DEV_VERSION='2.0.0' ; \
  echo ">> Checkout branches" && \
  git checkout master && \
  git fetch --all && \
  git pull --rebase && \
  git checkout ${RELEASE_BRANCH} && \
  git pull --rebase && \
  echo ">> Create release ${RELEASE_VERSION}" && \
  git checkout -b release-${RELEASE_VERSION} && \
  sed -i "s/^COMPOSER_ROOT_VERSION.*/COMPOSER_ROOT_VERSION=\"${RELEASE_VERSION}\"/" Build/Scripts/runTests.sh && \
  sed -i "s/^  RELEASE_VERSION.*/  RELEASE_VERSION='${RELEASE_VERSION}' ; \\\\/" README.md && \
  sed -i "s/^  DEV_VERSION.*/  DEV_VERSION='${DEV_VERSION}' ; \\\\/" README.md && \
  tailor set-version ${RELEASE_VERSION} && \
  composer config "extra"."typo3/cms"."version" "${RELEASE_VERSION}" && \
  echo "${RELEASE_VERSION}" > VERSION && \
  git add . && \
  git commit -m "[RELEASE] ${RELEASE_VERSION}" && \
  git push --set-upstream origin release-${RELEASE_VERSION} && \
  gh pr create --fill --base ${RELEASE_BRANCH} --title "[RELEASE] ${RELEASE_VERSION}" && \
  sleep 10 && \
  gh pr checks --watch --interval 2 && \
  sleep 10 && \
  gh pr merge -rd --admin && \
  git remote prune origin && \
  git tag ${RELEASE_VERSION} && \
  git push origin ${RELEASE_VERSION} && \
  echo ">> Post-release - set dev version: ${DEV_VRESION}-dev" && \
  git checkout -b set-dev-version-${DEV_VERSION} && \
  sed -i "s/^COMPOSER_ROOT_VERSION.*/COMPOSER_ROOT_VERSION=\"${DEV_VERSION}-dev\"/" Build/Scripts/runTests.sh && \
  tailor set-version ${DEV_VERSION} && \
  composer config "extra"."typo3/cms"."version" "${DEV_VERSION}-dev" && \
  echo "${DEV_VERSION}-dev" > VERSION && \
  git add . && \
  git commit -m "[TASK] Set dev version ${DEV_VERSION}" && \
  git push --set-upstream origin set-dev-version-${DEV_VERSION} && \
  gh pr create --fill --base ${RELEASE_BRANCH} --title "[TASK] Set dev version \"${DEV_VERSION}-dev\"" && \
  sleep 10 && \
  gh pr checks --watch --interval 2 && \
  sleep 10 && \
  gh pr merge -rd --admin && \
  git remote prune origin
```

This triggers the `on push tags` workflow (`publish.yml`) which creates the upload package,
creates the GitHub release and also uploads the release to the TYPO3 Extension Repository.
