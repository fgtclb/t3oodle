# AGENTS.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`fgtclb/t3oodle` — a TYPO3 CMS extension (extension key `t3oodle`) providing a
frontend poll/voting plugin (Doodle-like). Frontend users create polls and vote
on existing ones. Namespace `FGTCLB\T3oodle\` → `Classes/`.

## Version / branch matrix

| Branch   | Version | TYPO3 | PHP                |
|----------|---------|-------|--------------------|
| `master` | 3.x     | v13.4 | 8.2, 8.3, 8.4, 8.5 |
| `2`      | 2.x     | v12   | 8.1, 8.2, 8.3, 8.4 |
| `1`      | 1.x     | v11   | 8.1, 8.2, 8.3, 8.4 |

`master` is v13-only. `runTests.sh` has a `-t <13>` option, which only selects
the core configuration (e.g. `Build/phpstan/Core13/`) and currently accepts
`13` only. The version lives in four
places kept in sync at release: `composer.json` (`extra.typo3/cms.version`),
`ext_emconf.php`, `VERSION`, and `COMPOSER_ROOT_VERSION` in `runTests.sh`.

## Commands

All checks run through the containerized core test runner
(`Build/Scripts/runTests.sh`, docker or podman). First run needs dependencies:

```bash
Build/Scripts/runTests.sh -s composerUpdate   # fresh install of the deps into .Build/
```

Common suites (`-s`):

```bash
Build/Scripts/runTests.sh -s cgl                  # php-cs-fixer: fix all files (use -n for dry-run/check)
Build/Scripts/runTests.sh -s cgl -n               # CGL check only (CI mode, no changes)
Build/Scripts/runTests.sh -s phpstan              # PHPStan analyse
Build/Scripts/runTests.sh -s phpstanGenerateBaseline
Build/Scripts/runTests.sh -s lintPhp              # PHP lint
Build/Scripts/runTests.sh -s lintTypoScript       # TypoScript lint
Build/Scripts/runTests.sh -s checkBom             # UTF-8 BOM check
Build/Scripts/runTests.sh -s checkExceptionCodes  # duplicate exception code check
Build/Scripts/runTests.sh -s unit                 # unit tests
Build/Scripts/runTests.sh -s functional -d sqlite # functional tests (db: sqlite|mariadb|mysql|postgres)
```

Select PHP with `-p` (e.g. `-p 8.3`); functional DB driver with `-a mysqli|pdo_mysql`.

Run a single test (pass a path, PHPUnit args go after `--` and before the path):

```bash
Build/Scripts/runTests.sh -s unit Tests/Unit/Path/To/SomeTest.php
Build/Scripts/runTests.sh -s functional -d sqlite -- --filter someMethodName
```

CI mirrors these in `.github/workflows/testcore13.yml` (`testcore12.yml` for branch
`2` and `testcore11.yml` for branch `1`).

> Note: Suites live in `Tests/Unit` and `Tests/Functional` (bootstraps in
> `Build/phpunit/`). The test coverage is still small; add tests for new code.

## Architecture

**Single plugin, single controller.** One plugin `T3oodle`/`Main` registered in
`ext_localconf.php`, served entirely by `Classes/Controller/PollController.php`
(a large `final` ActionController covering list/show/vote/new/create/edit/update/
publish/finish + suggestion-mode and own-vote actions).

**Poll types via Single Table Inheritance (STI).** `Domain/Model/BasePoll`
(abstract) is the Extbase entity persisted to `tx_t3oodle_domain_model_poll`;
concrete `SimplePoll` and `SchedulePoll` are recordType subclasses. New poll
types are added by external extensions extending `BasePoll` — see
`Documentation/DevNotes/SingleTableInheritance.rst` for the full recipe (TCA
override, Extbase subclass mapping, partial, locallang keys, routing aspect).
`Extbase/TypeConverter/BasePollObjectConverter` resolves request data to the
right concrete subclass (registered in `Configuration/Services.yaml`).

**Convention-based validation.** A poll model `…\Model\XyzPoll` is validated by
`…\Validator\XyzPollValidator` if that class exists — **no validator means
validation is silently disabled** for that type. `PollController` uses
`Traits/ControllerValidatorManipulatorTrait` to add/remove validators on
arguments at runtime (e.g. disabling generic-object validation per action).

**Central permission gate.** `Domain/Permission/PollPermission` holds every
`isXxxAllowed(...)` decision; it's consumed by both controller actions and Fluid
templates via `ViewHelpers/PermissionViewHelper` (`t3oodle:permission(action:'…')`,
name given without the `is`/`Allowed` affixes). See `DevNotes/PollPermission.rst`.

**Extensibility — two parallel mechanisms.** PSR-14 events in `Classes/Event/`
(domain lifecycle: `Create/Update/Delete/Vote/Publish/Finish…`, repository
`PollRepository/FindPollsEvent`, and `Event/Permission/*` to override permission
results). Signals and slots are not supported anymore, use PSR-14 events (documented in
`Documentation/DevNotes/Events.rst`).
`EventListener/UpdatePollSlugListener` (registered via `#[AsEventListener]`)
regenerates the poll slug on create/update.

**User abstraction.** Polls track a creator that may be a real FE user or an
anonymous/cookie-based identity — `Traits/Model/DynamicUserProperties`,
`Service/UserIdentService`, `Utility/CookieUtility`, and `Service/UserService`
handle this. `PollFrontendUser` is the FE-user model.

**TCA is partly generated.** `Configuration/TCA/*` define the four tables
(`poll`, `option`, `optionvalue`, `vote`); `Utility/TcaGeneratorUtility` builds
select `items` arrays from the `Domain/Enumeration/*` enums (`PollStatus`,
`Visibility`). `ext_tables.sql` defines the schema.

## DI note (migration target)

Event listeners are registered with the **`#[AsEventListener]`** attribute.
The Extbase type converter is still tagged in **`Configuration/Services.yaml`**,
which deviates from the preferred standard of PHP attributes. Treat this as a
migration target: for new code and for existing services as you touch them,
prefer attributes and trim `Services.yaml` accordingly.

Constraints before using any attribute:

- Prefer Symfony DI attributes (e.g. `#[Autoconfigure]`, `#[Autowire]`). TYPO3
  attributes (e.g. `#[AsEventListener]`) are fine, if they exist in TYPO3 v13.
- Confirm an attribute is **safe to use before applying it**: it must exist and
  behave identically across **every TYPO3 version supported by the branch**
  (`master` is v13-only; branch `2` is v12, branch `1` is v11). If an attribute is not available in
  every supported version, do not use it — fall back to `Services.yaml`.
- When in doubt about availability/behaviour, ask rather than assume.

## Conventions

- Every PHP file carries the GPL license header block (`(c) 2020-2021 Armin Vieweg`).
- Concrete domain models, controller, and many classes are `final`.
- CGL is php-cs-fixer driven (`Build/php-cs-fixer/php-cs-rules.php`); always run
  `-s cgl` before committing.
- Exception codes must be unique (enforced by `checkExceptionCodes`).
- Commit messages follow the TYPO3 Core rules (subject tag, ≤52-char subject,
  72-char wrap); the `Resolves:`/`Releases:` footer may be skipped for this
  extension — see release recipe in `README.md`.
