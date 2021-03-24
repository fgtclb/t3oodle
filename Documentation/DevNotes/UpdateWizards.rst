.. include:: ../Includes.txt


.. _updateWizards:


Update Wizards
==============

t3oodle ships currently one upgrade wizard, to make it easier to apply structural changes.


Migrate old "One option only" setting
-------------------------------------

In t3oodle 0.6 the poll setting "oneOptionOnly" has been changed to "maxVotesPerParticipant".
This update wizard checks "tx_t3oodle_domain_model_poll" table for old setting set and migrates it to new option.
