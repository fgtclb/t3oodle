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

| Branch | Version | TYPO3 | PHP                                               |
|--------|---------|-------|---------------------------------------------------|
| main   | 1.x-dev | v11   | 8.1, 8.2, 8.3, 8.4 (depending on TYPO3)           |

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

**Prepare release locally**

> Set `RELEASE_BRANCH` to branch release should happen, for example: 'master'.
> Set `RELEASE_VERSION` to release version working on, for example: '1.0.0'.

```shell
echo '>> Prepare release pull-request' ; \
  RELEASE_BRANCH='master' ; \
  RELEASE_VERSION='1.0.0' ; \
  git checkout main && \
  git fetch --all && \
  git pull --rebase && \
  git checkout ${RELEASE_BRANCH} && \
  git pull --rebase && \
  git checkout -b prepare-release-${RELEASE_VERSION} && \
  composer require --dev "typo3/tailor" && \
  ./.Build/bin/tailor set-version ${RELEASE_VERSION} && \
  composer remove --dev "typo3/tailor" && \
  git add . && \
  git commit -m "[RELEASE] ${RELEASE_VERSION}" && \
  git push --set-upstream origin prepare-release-${RELEASE_VERSION} && \
  gh pr create --fill-verbose --base ${RELEASE_BRANCH} --title "[RELEASE] ${RELEASE_VERSION} on ${RELEASE_BRANCH}" && \
  git checkout main && \
  git branch -D prepare-release-${RELEASE_VERSION}
```

Check pull-request and the pipeline run.

**Merge approved pull-request and push version tag**

> Set `RELEASE_PR_NUMBER` with the pull-request number of the preparation pull-request.
> Set `RELEASE_BRANCH` to branch release should happen, for example: 'master' (same as in previous step).
> Set `RELEASE_VERSION` to release version working on, for example: `1.0.0` (same as in previous step).

```shell
RELEASE_BRANCH='master' ; \
RELEASE_VERSION='1.0.0' ; \
RELEASE_PR_NUMBER='123' ; \
  git checkout main && \
  git fetch --all && \
  git pull --rebase && \
  gh pr checkout ${RELEASE_PR_NUMBER} && \
  gh pr merge -rd ${RELEASE_PR_NUMBER} && \
  git tag ${RELEASE_VERSION} && \
  git push --tags
```

This triggers the `on push tags` workflow (`publish.yml`) which creates the upload package,
creates the GitHub release and also uploads the release to the TYPO3 Extension Repository.
