.. include:: ../Includes.txt


.. _updateWizards:


Update Wizards
==============

t3oodle ships an update wizard, to make it easier to apply structural changes.
Run it in :guilabel:`Admin Tools > Upgrade > Upgrade Wizard` or on CLI:

.. code-block:: bash

   typo3 upgrade:run fgtclbT3oodleCTypeMigration


.. _updateWizardsCType:

Migrate plugin to content element
---------------------------------

Identifier: ``fgtclbT3oodleCTypeMigration``

Up to t3oodle 2.x, the plugin has been registered as plugin (``CType = list`` and ``list_type = t3oodle_main``).
Since t3oodle 3.0, it is registered as content element with ``CType = t3oodle_main``, which is the way TYPO3 v13
wants plugins to be registered.

The update wizard migrates existing content elements and the permissions of backend users and groups.

.. important::
   Run this wizard after updating from t3oodle 2.x. Otherwise, existing plugins on your pages are not rendered anymore.

.. note::
   The wizards for older changes (migration of the ``oneOptionOnly`` setting and of the poll types ``simple`` and
   ``schedule``) have been removed. If you update from a version older than 1.0, update to t3oodle 2.x first and run
   the wizards there.
