# TYPO3 CMS Extension: t3oodle

Provides polls for Frontend Users in TYPO3 CMS.


## Documentation

This extension provides a ReST documentation, located in ``Documentation/`` directory.

You can see a rendered version on https://docs.typo3.org/p/t3/t3oodle.


## Development

### .ddev Environment

See https://github.com/a-r-m-i-n/ddev-for-typo3-extensions

#### First start

```
ddev install-all
```

#### Reset Environment
```
ddev rm -O -R
docker volume rm t3oodle-v8-data t3oodle-v9-data t3oodle-v10-data
```
