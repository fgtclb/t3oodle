.. include:: ../Includes.txt


.. _versions:


Versions
========

0.3.0
-----

- [TASK] Use "poll-{uid}" as slug, when sanitized title is empty (e.g. Emojis only)
- [BUGFIX] Fix updated namespace in ext_localconf.php
- [FEATURE] Add "requireAcceptedTerms" and "termsToAccept" options
- [!!!][TASK] Update vendor name in PHP classes
- [DOCS] Add credits to README and documentation index page
- [TASK] Update package name from "t3/t3oodle" to "fgtclb/t3oodle"
- [DOCS] Improvements
- [FEATURE] Add pluralization support for XLIFF
- [TASK] Remove typoscript settings "list.opened" and "list.closed"
- [BUGFIX] Do not allow to delete votes, when poll is finished
- [TASK] Disable "outputGuestNotice" by default
- [FEATURE] Respect available options (when settingMaxVotesPerOption is set)
- [BUGFIX] Fix wrong parameter in deleteVoteAction


0.2.2
-----

- [BUGFIX] Fix missing argument in Fluid section
- [FEATURE] Refactor PollPermissions and add Signals to each permission
- [TASK] Add license to composer.json
- [BUGFIX] Move allowTableOnStandardPages from TCA to ext_tables.php


0.2.1
-----

- [FEATURE] Add "_dynamic" setting
- [TASK] Add "getContentObjectRow" method to PollController
- [TASK] Pass "view" to all action signals with templates existing
- [TASK] Do not pass items to asynchronous loaded function calls


0.2.0
-----

- [TASK] Apply copyrights
- [TASK] Apply PSR-2 code style
- [TASK] Add "UpdatePollSlug" Slot
- [FEATURE] Add Signals to PollController
- [TASK] Show notice when current user already voted for a poll
- [FEATURE] Add option "enableFlashMessages"
- [BUGFIX] Fix wrong fieldname for validation messages of "settingMaxVotesPerOption"
- [FEATURE] Add translations for flash & validation messages and exceptions
- [TASK] Improve conditional inputs
- [FEATURE] Add option "countMaybeVotes"
- [TASK] Make columns in list view configurable (via TypoScript)
- [FEATURE] Add translations and improvements for show view
- [FEATURE] Add translations and improvements for new and edit view
- [FEATURE] Add translations and improvements for list view
- [BUGFIX] Fix PollValidator to not check "null" in preg_match
- [BUGFIX] Fix missing default value for "poll-type" aspect
- [BUGFIX] Do not add empty option when editing schedule poll
- [FEATURE] Add extension icon
- [TASK] Prepare FlexForm plugin settings
- [TASK] Added locale to Routing Enhancer
- [BUGFIX] Show selected options, when validation fails (vote action)
- [TASK] Make properties accessible for debugging tools
- [FEATURE] Provide custom bootstrap build
- [BUGFIX] Fix voting box querySelector
- [TASK] Introduce ScheduleOptionUtility
- [BUGFIX] Fix missing "publishDirectly" variable in Fluid section
- [FEATURE] Add poll type "schedule"
- [TASK] Refactor PollValidator
- [BUGFIX] Stop event propagation, when clicking on voting box image
- [TASK] Show if current user author if poll (in list action)
- [TASK] Apply click listener to voting box' parentNode (e.g. <td>)
- [FEATURE] Enable radio-like behaviour, when setting "OneOptionOnly" is set
- [TASK] Refactor fluid templates
- [TASK] Lock username and mail input, when userIdent cookie exists
- [TASK] Add class to voting box parent node
- [TASK] Refactor fluid partials
- [TASK] Small improvements
- [TASK] Add "pollType" argument to new action
- [BUGFIX] Fix selector in options-simple module
- [TASK] Refactor javascript


0.1.*
-----

- [TASK] Clean up TCA
- [BUGFIX] Use ParticipantInfo partial everywhere
- [TASK] Add Routing Enhancer Configuration for pagination widget
- [TASK] Refactor fluid templates
- [TASK] Introduce SVG view helper & icons
- [FEATURE] Add configurable classes
- [BUGFIX] Fix wrong variable in template and do not iterate over null
- [FEATURE] Add "DynamicUserProperties" trait


0.1.0
-----

- Very first release of t3oodle, for testing purposes. Many open (cleanup) tasks, left.
