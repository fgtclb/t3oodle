TYPO3 CMS Extension: t3oodle
============================

Simple poll extension for TYPO3 CMS. t3oodle allows your frontend users
to create new polls and vote for existing ones.

This extension has been brought to you by **FGTCLB** and has been supported by **Friedrich-Ebert-Stiftung e. V.**

.. image:: Documentation/Welcome/Images/FGTLB.svg
  :width: 21%
  :target: https://www.fgtclb.com/
  :alt: FGTCLB

.. image:: Documentation/Welcome/Images/Spacer.svg
  :width: 50
  :target: #

.. image:: Documentation/Welcome/Images/FES.svg
  :width: 40%
  :target: https://www.fes.de/
  :alt: Friedrich-Ebert-Stiftung e. V.


Documentation
-------------

This extension provides a ReST documentation, located in ``Documentation/`` directory.

You can see a rendered version on https://docs.typo3.org/p/fgtclb/t3oodle once the extension has been released.


Demo
----

You will find a demonstration of the extension on https://t3oodle.com


Development
-----------

.ddev Environment
~~~~~~~~~~~~~~~~~

See https://github.com/a-r-m-i-n/ddev-for-typo3-extensions

First start
^^^^^^^^^^^

::

    ddev install-all


Reset Environment
^^^^^^^^^^^^^^^^^

::

    ddev rm -O -R
    docker volume rm t3oodle-v8-data t3oodle-v9-data t3oodle-v10-data
