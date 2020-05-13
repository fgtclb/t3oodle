# TYPO3 CMS Extension: t3oodle

Provides polls for Frontend Users in TYPO3 CMS.


## Plannings 

### Entities

* **Poll:**
    * Title
    * Slug
    * Visibility (public, secret, private)
    * Author (User)
    * Users
    * Settings
        - Send mail with link to user
        - Third option (bool, false) [yes, no, maybe]
        - Limit votes per option (int, 0)
        - One option only (bool, false)
        - Limit voting by datetime
            - Send reminder mail n days before limit reached
    * Options (1:n)

* **Option:**
    * *Parent Poll*
    * Name
    * Votes
    * Selected (when vote ends)
    
* **Vote:**
    * User
    * Value (yes, no, maybe)


### Controller/Actions

* **Poll**
    *  list
    *  show(Poll)
    *  new
    *  edit(Poll)
    *  delete(Poll)

* **Vote**
    * new (Poll, Vote)
    * edit (Poll, Vote)


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
