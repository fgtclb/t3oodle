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
- ``isFinishSuggestionModeAllowed($poll)``
- ``isSuggestNewOptionsAllowed($poll)``
- ``isVotingAllowed($poll)``
- ``isSeeParticipantsDuringVotingAllowed($poll)``
- ``isSeeVotesDuringVotingAllowed($poll)``
- ``isAdministrationAllowed($poll)``
- ``isResetVotesAllowed($poll)``
- ``isDeleteOwnVoteAllowed($vote)`` **!!!**

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

Permissions can get changed programmatically by using :ref:`PSR-14 events <events>`.
For most permissions an own event exists (see :ref:`events-permission`). The event carries the current status, which
you can overwrite with ``setAllowed()``.

For example, to modify the permission to create a new poll, listen to ``NewPollAllowedEvent``:

.. code-block:: php

    <?php

    declare(strict_types=1);

    namespace Vendor\MyExt\EventListener;

    use FGTCLB\T3oodle\Event\Permission\NewPollAllowedEvent;
    use TYPO3\CMS\Core\Attribute\AsEventListener;

    final class NewPollPermissionListener
    {
        #[AsEventListener(identifier: 'my-ext/new-poll-permission')]
        public function __invoke(NewPollAllowedEvent $event): void
        {
            if (!$event->isAllowed()) {
                return;
            }
            // TODO: implement your own logic
            $event->setAllowed(false);
        }
    }

.. note::
   Not every permission method of ``PollPermission`` dispatches an event. Have a look in
   :file:`Classes/Event/Permission/` for the available events.
