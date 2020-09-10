.. include:: ../Includes.txt


.. _signalSlots:


Signal/Slots
============

Every action in t3oodle controllers implemented a **signal** you can use to put slots in it and modify or extend
extension behaviour.


Existing signals
----------------

The following signals exist:

``\FGTCLB\T3oodle\Controller\PollController``

* **list**
* **show**
* **vote**
* **deleteOwnVote**
* **resetVotes**
* **showFinish**
* **finish**
* **new**
* **createBefore**
* **createAfter**
* **publish**
* **edit**
* **updateBefore**
* **updateAfter**
* **delete**


``\FGTCLB\T3oodle\Domain\Permission\PollPermission``

* Also all permissions defined in :ref:`pollPermission` dispatch an own signal


Register a slot
---------------

To register a slot, you just need to add the following PHP code in ``ext_localconf.php`` of a TYPO3 extension:

::

    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \FGTCLB\T3oodle\Controller\PollController::class,
        'vote',
        \Vendor\MyExt\Slots\MyCustomSlots::class,
        'vote'
    );


Writing the slot
----------------

The method you define as slot requires all arguments, passed by dispatcher.
To identify the correct arguments, search in t3oodle code for ``->dispatch(__CLASS__``.

For example the arguments passed to vote action slot are:

::

    [
        'vote' => $vote,
        'isNew' => !$vote->getUid(),
        'settings' => $this->settings,
        'continue' => true,
        'caller' => $this
    ]


So, this is how the slot method signature must look like:

::

    use FGTCLB\T3oodle\Domain\Model\Vote;

    final class MyCustomSlots
    {
        public function vote(\FGTCLB\T3oodle\Domain\Model\Vote $vote, bool $isNew, array $settings, bool $continue, PollPermission $caller): array
        {
            // TODO: implement me
            return [
                'vote' => $vote,
                'isNew' => $isNew,
                'settings' => $settings,
                'continue' => $continue,
                'caller' => $caller,
            ];
        }
    }

Also, the slot  **needs** to return the arguments in **same order**, with **keys prefixed**!


Continue argument
~~~~~~~~~~~~~~~~~

Some signals pass a ``continue`` argument, which is set to ``true`` by default.

When you set this value to ``false`` in your slot, the original code following, is not executed anymore.
You can use this to prevent flash messages and redirects.
