.. include:: ../Includes.txt


.. _pollPermission:


PollPermission
==============

t3oodle has got a central class controlling poll permission logic.

**Existing permissions are:**

- ``isViewingInGeneralAllowed($poll)``
- ``isViewingAllowed($poll)``
- ``isShowAllowed($poll)``
- ``isNewAllowed()``
- ``isNewSimplePollAllowed()``
- ``isNewSchedulePollAllowed()``
- ``isEditAllowed($poll)``
- ``isDeleteAllowed($poll)``
- ``isPublishAllowed($poll)``
- ``isFinishAllowed($poll)``
- ``isVotingAllowed($poll)``
- ``isSeeParticipantsDuringVotingAllowed($poll)``
- ``isSeeVotesDuringVotingAllowed($poll)``
- ``isAdministrationAllowed($poll)``
- ``isDeleteVoteAllowed($vote)`` **!!!**

They are used in controller actions, as well as in Fluid templates.


Permission ViewHelper
---------------------

::

    <f:if condition="{t3oodle:permission(action:'newSimplePoll')}">
        // show new button
    </f:if>

The action is passed without trailing **is** and **Allowed**.

When a permission requires poll or vote argument, you can call it like this:

::

    <f:if condition="{poll -> t3oodle:permission(action:'delete')}">
        // show delete button, for this poll
    </f:if>


Modifying permissions
---------------------

Every permission can get changed programmatically, by using :ref:`signalSlots`.

First register your slot in ``ext_localconf.php``:

::

    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \FGTCLB\T3oodle\Domain\Permission\PollPermission::class,
        'isNewSimplePollAllowed',
        \Vendor\MyExt\Slots\PollPermissionSlots::class,
        'isNewSimplePollAllowed'
    );


The ``PollPermissionSlots`` class looks like this:

::

    final class PollPermissionSlots
    {
        public function isNewSimplePollAllowed(bool $currentStatus, array $arguments, PollPermission $caller): array
        {
            $newStatus = false; // TODO: implement me
            return [
                'currentStatus' => $newStatus,
                'arguments' => $arguments,
                'caller' => $caller,
            ];
        }
    }
