.. include:: ../Includes.txt


.. _events:


Events
======

Every action in t3oodle controllers dispatches a `PSR-14 event <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Events/EventDispatcher/Index.html>`__
you can listen to, to modify or extend the extension behaviour.

.. note::
   Up to t3oodle 0.x the extension used Extbase signals and slots. They have been replaced by PSR-14 events.
   Signals and slots are not supported anymore.


Controller events
-----------------

All events are located in the namespace ``\FGTCLB\T3oodle\Event``. The controller events are dispatched by
``\FGTCLB\T3oodle\Controller\PollController``.

========================================= ==============================================================
Event                                     Dispatched
========================================= ==============================================================
``ListPollEvent``                         List action
``ShowPollEvent``                         Show action
``ShowFinishEvent``                       Finish form is displayed
``VotePollEvent``                         A vote is stored
``DeleteOwnVoteEvent``                    A participant deletes the own vote
``ResetVotesEvent``                       All votes of a poll are reset
``NewPollEvent``                          New action (form to create a poll)
``CreateBeforeEvent``                     Before a new poll is persisted
``CreateAfterEvent``                      After a new poll has been persisted
``EditPollEvent``                         Edit action (form to update a poll)
``UpdateBeforeEvent``                     Before a poll is updated
``UpdateAfterEvent``                      After a poll has been updated
``PublishPollEvent``                      A poll gets published
``FinishPollEvent``                       A poll gets finished
``DeletePollEvent``                       A poll gets deleted
``FinishSuggestionModeEvent``             The suggestion mode of a poll gets finished
``NewSuggestionEvent``                    Form to suggest a new option
``CreateSuggestionBeforeEvent``           Before a suggestion is persisted
``CreateSuggestionAfterEvent``            After a suggestion has been persisted
``EditSuggestionEvent``                   Form to edit a suggestion
``UpdateSuggestionBeforeEvent``           Before a suggestion is updated
``UpdateSuggestionAfterEvent``            After a suggestion has been updated
``DeleteSuggestionEvent``                 A suggestion gets deleted
========================================= ==============================================================

Most events provide the affected object (e.g. ``getPoll()``, ``getVote()``), the TypoScript ``getSettings()`` and the
calling controller (``getCaller()``). Events for actions which render a template also provide the view
(``getView()``), so you can assign own variables to it.


.. _events-continue:

Continue argument
~~~~~~~~~~~~~~~~~

Several events provide a ``setContinue()`` method (for example ``VotePollEvent``, ``CreateAfterEvent``,
``UpdateBeforeEvent`` or ``DeletePollEvent``). By default, it is ``true``.

When you set it to ``false`` in your listener, the original code following in the controller, is not executed anymore.
You can use this to prevent flash messages and redirects. Check the event class for the available methods.


.. _events-permission:

Permission events
-----------------

The permission events are located in ``\FGTCLB\T3oodle\Event\Permission``. They allow you to overrule the
decision about a permission, see :ref:`pollPermission`. Every permission event provides ``isAllowed()`` and
``setAllowed(bool $allowed)``.

* ``CreatePollAllowedEvent``
* ``DeletePollAllowedEvent``
* ``DeleteSuggestionAllowedEvent``
* ``EditPollAllowedEvent``
* ``EditSuggestAllowedEvent``
* ``FinishPollAllowedEvent``
* ``FinishSuggestionModeAllowedEvent``
* ``NewOptionsAllowedEvent``
* ``NewPollAllowedEvent``
* ``PublishPollAllowedEvent``
* ``ShowPollAllowedEvent``
* ``SuggestNewOptionsAllowedEvent``
* ``UpdateSuggestionAllowedEvent``
* ``VoteAllowedEvent``
* ``VoteDeleteAllowedEvent``
* ``VoteResetAllowedEvent``


Repository event
----------------

``\FGTCLB\T3oodle\Event\PollRepository\FindPollsEvent`` is dispatched by ``PollRepository::findPolls()`` and allows you
to modify the query constraints:

* ``getConstraints()`` / ``setConstraints(array $constraints)``
* ``getQuery()`` to build own constraints


Register a listener
-------------------

Create a listener class in your extension and register it with the ``AsEventListener`` attribute:

.. code-block:: php

    <?php

    declare(strict_types=1);

    namespace Vendor\MyExt\EventListener;

    use FGTCLB\T3oodle\Event\VotePollEvent;
    use TYPO3\CMS\Core\Attribute\AsEventListener;

    final class VoteListener
    {
        #[AsEventListener(identifier: 'my-ext/t3oodle-vote')]
        public function __invoke(VotePollEvent $event): void
        {
            $vote = $event->getVote();
            $isNew = $event->getIsNew();

            // TODO: implement me

            // Prevent the code following in controller, e.g. flash messages and redirect
            // $event->setContinue(false);
        }
    }

Alternatively, you can register listeners with the ``event.listener`` tag in :file:`Configuration/Services.yaml`.
t3oodle itself ships a listener, which you can use as reference: ``\FGTCLB\T3oodle\EventListener\UpdatePollSlugListener``.
