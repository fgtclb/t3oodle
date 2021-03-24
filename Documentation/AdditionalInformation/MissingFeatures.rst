.. include:: ../Includes.txt


.. _missingFeatures:


Missing features
================

* Notifications
* Invite mechanism
* Restoring userIdent (When a guest looses its userIdent cookie, poll administration becomes impossible)
* Spam protection
* Responsive voting table


Upcoming, breaking changes
--------------------------

* Add UNIQUE constraints to schema (may break, when invalid state already existing)
* Rename "findByPollAndParticipantIdent" to "findOneByPollAndParticipantIdent"
