.. include:: ../Includes.txt


.. _singleTableInheritance:


Single Table Inheritance for polls
==================================

Since v0.9 t3oodle provides single table inheritance for polls. This allows you to provide own poll types in extensions, easier.

Extbase models t3oodle provide:

- ``\FGTCLB\T3oodle\Domain\Model\BasePoll`` (abstract)

  - ``\FGTCLB\T3oodle\Domain\Model\SimplePoll`` (final)
  - ``\FGTCLB\T3oodle\Domain\Model\SchedulePoll`` (final)


How to provide new poll type?
-----------------------------

1. Create a new Extbase model extending from ``\FGTCLB\T3oodle\Domain\Model\BasePoll``

2. Overwrite the property ``protected $typeName = 'Whatever';`` with an UpperCamelCase identifier (used in templates and localization identifiers)

3. Create new TCA override (``EXT:your_ext/Configuration/TCA/Overrides/tx_t3oodle_domain_model_poll.php``);
   ::

       $GLOBALS['TCA']['tx_t3oodle_domain_model_poll']['types'][\Vendor\YourExt\Domain\Model\WhateverPoll::class] = [
           'showitem' => $GLOBALS['TCA']['tx_t3oodle_domain_model_poll']['types'][\FGTCLB\T3oodle\Domain\Model\SimplePoll::class]['showitem']
       ];

       $GLOBALS['TCA']['tx_t3oodle_domain_model_poll']['columns']['type']['config']['items'][] = [
           'Whatever Poll',
           \Vendor\YourExt\Domain\Model\WhateverPoll::class
       ];

4. Extend Extbase mapping:
   ::

       config.tx_extbase {
           persistence {
               classes {
                   FGTCLB\T3oodle\Domain\Model\BasePoll {
                       subclasses {
                           \Vendor\YourExt\Domain\Model\WhateverPoll = Vendor\YourExt\Domain\Model\WhateverPoll
                       }
                   }
                   Vendor\YourExt\Domain\Model\WhateverPoll {
                       mapping {
                           tableName = tx_t3oodle_domain_model_poll
                           recordType = Vendor\YourExt\Domain\Model\WhateverPoll
                       }
                   }
               }
           }
       }


   .. note::
      This example is for TYPO3 v9, written in TypoScript. In v10 you should use PHP syntax instead.

5. When you use Routing Enhancer, you need to add the new poll type, to routing aspect ``poll-type`` map and localeMap

6. Add a partial for the new typeName e.g. ``EXT:your_ext/Resources/Private/Partials/Poll/FormFields/Options/Type/Whatever.html``,
   keep in mind that you also need to register the partial path in t3oodle's TypoScript

7. There are also several translation keys (in locallang.xlf), which have the typeName appended:

   - ``header.new.``
   - ``header.edit.``
   - ``header.show.``
   - ``poll.type.``

8. To provide validation for your new poll object, you just need to provide an Extbase validator matching this naming convention:

   - ``\Vendor\YourExt\Domain\Model\WhateverPoll`` <- when this is your poll entity
   - ``\Vendor\YourExt\Domain\Validator\WhateverPollValidator`` <- this is the validator to be expected

   .. important::
      If the validator is not existing, validation for this poll entity is disabled!
