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

After **the first** ``ddev start`` you just need to perform this script locally:

```
ddev init
```

Then you can access https://t3oodle.ddev.site/


### Documentation

To render the provided documentation locally, use the following command:

```
ddev docs
```

Generated docs are available under: ``Documentation-GENERATED-temp/Result/project/0.0.0/Index.html``

To open the docs in browser (locally), you can use:

```
ddev view-docs
```
