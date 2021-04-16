.. include:: ../Includes.txt


.. _updateWizards:


Update Wizards
==============

t3oodle ships several update wizards, to make it easier to apply structural changes.


Migrate old "One option only" setting
-------------------------------------

In t3oodle 0.6 the poll setting "oneOptionOnly" has been changed to "maxVotesPerParticipant".
This update wizard checks "tx_t3oodle_domain_model_poll" table for old setting set and migrates it to new option.


Migrate old poll types
----------------------

Since t3oodle 0.9 the poll types are using Single Table Inheritance. In order to make this pattern to work in Extbase,
the type field must contain the FQCN of the entity model to be used. This migration, converts "simple" and "schedule" to
corresponding class names.
