.. include:: ../Includes.txt


.. _slugGeneration:


Slug generation
===============

Every poll got an own **slug**, which

* is based on entered title (when listing is allowed)
* is randomly created (when listing is disallowed)

Good to know
------------

* When listing setting of poll gets updated during edit, also the slug gets updated.
* t3oodle uses a slot for this, registered with ``createAfter`` and ``updateBefore`` signal of PollController.
  See :ref:`signalSlots` for further info.
* When a slug wouldn't be unique, the poll uid is appended.
