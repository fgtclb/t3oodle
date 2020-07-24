.. include:: ../Includes.txt


.. _usagePollStatus:


Poll status
-----------

A poll have a status which is calculated dynamically based on several indicators.

The following status exist:


.. _pollStatusDraft:

Draft
~~~~~
.. container:: table-row

   Description
      Then the poll has been created but is **not published** yet, it got the status "draft".


.. _pollStatusFinished:

Finished
~~~~~~~~
.. container:: table-row

   Description
      A poll is finished, when the poll author selected a final option.


.. _pollStatusOpened:

Opened
~~~~~~
.. container:: table-row

   Description
      The poll is open for new votings!


.. _pollStatusClosed:

Closed
~~~~~~
.. container:: table-row

   Description
      The poll is closed. No new votings allowed! There are several reasons, why a poll may get closed:

      - set expire date is reached
      - no available options left (when they are limited)
      - voting has been disabled, in general
