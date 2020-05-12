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

After ``ddev start`` you just need to perform this script locally:

```
./.ddev/web-build/initial-setup.sh
```

Then you can access https://t3oodle.ddev.site/
