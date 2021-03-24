.. include:: ../Includes.txt


.. _configuration:


Configuration
=============

The t3oodle extension provides several settings.

::

    plugin.tx_t3oodle.settings.


General
-------

===================================== ============ ======================================
Property                               Type         Default
===================================== ============ ======================================
dateTimeFormat_                        ``string``   %d.%m.%Y %H:%M
frontendUserNameField_                 ``string``   name
frontendUserMailField_                 ``string``   email
outputAuthorMails_                     ``bool``     1
outputParticipantMails_                ``bool``     1
outputGuestNotice_                     ``bool``     0
adminUserUids_                         ``string``
adminUserGroupUids_                    ``string``
countMaybeVotes_                       ``bool``     0
allowNewSimplePolls_                   ``bool``     1
allowNewSchedulePolls_                 ``bool``     1
allowSuggestionMode_                   ``bool``     1
requireAcceptedTerms_                  ``bool``     0
termsToAccept_                         ``string``
allowNewVotes_                         ``bool``     1
===================================== ============ ======================================



.. _dateTimeFormat:

dateTimeFormat
""""""""""""""
.. container:: table-row

   Property
      dateTimeFormat
   Data type
      string
   Default
      %d.%m.%Y %H:%M
   Description
      Date/time format used in all templates.


.. _frontendUserNameField:

frontendUserNameField
"""""""""""""""""""""
.. container:: table-row

   Property
      frontendUserNameField
   Data type
      string
   Default
      name
   Description
      Defines the field in ``fe_users`` table to use for the author's/participant's name.



.. _frontendUserMailField:

frontendUserMailField
"""""""""""""""""""""
.. container:: table-row

   Property
      frontendUserMailField
   Data type
      string
   Default
      email
   Description
      Defines the field in ``fe_users`` table to use for the author's/participant's mail address.



.. _outputAuthorMails:

outputAuthorMails
"""""""""""""""""
.. container:: table-row

   Property
      outputAuthorMails
   Data type
      bool
   Default
      1
   Description
      When enabled, outputs name of author and adds a mailto link, if mail is given. Just output name if disabled.


.. _outputParticipantMails:

outputParticipantMails
""""""""""""""""""""""
.. container:: table-row

   Property
      outputParticipantMails
   Data type
      bool
   Default
      1
   Description
      Same like outputAuthorMails setting, just for participants who voted on a poll.




.. _outputGuestNotice:

outputGuestNotice
"""""""""""""""""
.. container:: table-row

   Property
      outputGuestNotice
   Data type
      bool
   Default
      0
   Description
      When enabled, poll authors and participants without fe_users session are marked as "guest"






.. _adminUserUids:

adminUserUids
"""""""""""""
.. container:: table-row

   Property
      adminUserUids
   Data type
      string
   Description
      Comma separated list of **fe_users** uids, which should act as administrators.
      Admins have the same access to polls, like its author. Admins can edit or finish all polls.


.. _adminUserGroupUids:

adminUserGroupUids
""""""""""""""""""
.. container:: table-row

   Property
      adminUserGroupUids
   Data type
      string
   Description
      Same as adminUserGroupUids setting, just addressing **fe_groups**, instead of fe_users.



.. _countMaybeVotes:

countMaybeVotes
"""""""""""""""
.. container:: table-row

   Property
      countMaybeVotes
   Data type
      bool
   Default
      0
   Description
      When enabled, a "maybe" vote does count like a regular vote. Disabled by default.


.. _allowNewSimplePolls:

allowNewSimplePolls
"""""""""""""""""""
.. container:: table-row

   Property
      allowNewSimplePolls
   Data type
      bool
   Default
      1
   Description
      Enables/disables creation of new simple polls.


.. _allowNewSchedulePolls:

allowNewSchedulePolls
"""""""""""""""""""""
.. container:: table-row

   Property
      allowNewSchedulePolls
   Data type
      bool
   Default
      1
   Description
      Enables/disables creation of new scheduled polls.


.. _allowSuggestionMode:

allowSuggestionMode
"""""""""""""""""""
.. container:: table-row

   Property
      allowSuggestionMode
   Data type
      bool
   Default
      1
   Description
      Enables/disables suggestion mode setting, when creating new polls.


.. _requireAcceptedTerms:

requireAcceptedTerms
""""""""""""""""""""
.. container:: table-row

   Property
      requireAcceptedTerms
   Data type
      bool
   Default
      1
   Description
      When true, authors of new polls need to accept the terms.



.. _termsToAccept:

termsToAccept
"""""""""""""
.. container:: table-row

   Property
      termsToAccept
   Data type
      string
   Description
      When set "the terms" is linked with given Typolink parameter, e.g. ``t3://page?uid=3`` or ``https://www.domain.com``.



.. _allowNewVotes:

allowNewVotes
"""""""""""""
.. container:: table-row

   Property
      allowNewVotes
   Data type
      bool
   Default
      1
   Description
      Enables/disables new votings (on polls).


.. _enableFlashMessages:

enableFlashMessages
"""""""""""""""""""
.. container:: table-row

   Property
      enableFlashMessages
   Data type
      bool
   Default
      1
   Description
      Enables/disables flash messages being send from controllers (like "Poll successfully created").



View related settings
---------------------

================================================================================== ============ ========================
Property                                                                            Type         Default
================================================================================== ============ ========================
:ref:`list.draft <list_draft>`                                                      ``bool``     1
:ref:`list.finished <list_finished>`                                                ``bool``     1
:ref:`list.personal <list_personal>`                                                ``bool``     1
:ref:`list.itemsPerPage <list_itemsPerPage>`                                        ``int``      10
:ref:`list.showTotal <list_showTotal>`                                              ``bool``     1
:ref:`list.columns <list_columns>`                                                  ``array``    *see below*
:ref:`show.showReturnLink <show_showReturnLink>`                                    ``bool``     1
:ref:`show.showOwnVoteAtTop <show_showOwnVoteAtTop>`                                ``bool``     1
:ref:`show.showAdministrationBoxAboveVotes <show_showAdministrationBoxAboveVotes>`  ``bool``     1
:ref:`form.showReturnLink <form_showReturnLink>`                                    ``bool``     1
classes_                                                                            ``array``    *see below*
================================================================================== ============ ========================


.. _list_draft:

list.draft
""""""""""
.. container:: table-row

   Property
      list.draft
   Data type
      bool
   Default
      1
   Description
      When enabled, the list view contains polls which are not published yet.
      Draft polls are not public, only the poll author and admins can see them.

.. _list_finished:

list.finished
"""""""""""""
.. container:: table-row

   Property
      list.finished
   Data type
      bool
   Default
      1
   Description
      When enabled, the list view contains polls which are finished.


.. _list_personal:

list.personal
"""""""""""""
.. container:: table-row

   Property
      list.personal
   Data type
      bool
   Default
      1
   Description
      When enabled, the list view contains polls which only you can see (like pools, which are marked as "not listed" or
      which remain as draft).


.. _list_itemsPerPage:

list.itemsPerPage
"""""""""""""""""
.. container:: table-row

   Property
      list.itemsPerPage
   Data type
      int
   Default
      10
   Description
      Defines amount of polls to display per page, in list view. Set to ``0`` to disable pagination at all.


.. _list_showTotal:

list.showTotal
""""""""""""""
.. container:: table-row

   Property
      list.showTotal
   Data type
      bool
   Default
      1
   Description
      When enabled, the total amount of polls (with current settings) will be displayed above the list of polls.


.. _list_columns:

list.columns
""""""""""""
.. container:: table-row

   Property
      list.columns
   Data type
      array
   Description
      Here you can define the columns to be displayed in list view. By default, all columns are enabled:

      ::

          columns {
              type = 1
              status = 1
              options = 1
              author = 1
              lastChanges = 1
              participants = 1
          }



.. _show_showReturnLink:

show.showReturnLink
"""""""""""""""""""
.. container:: table-row

   Property
      show.showReturnLink
   Data type
      bool
   Default
      1
   Description
      When enabled, displays a link "back to list view" in detail view of a poll.

.. _show_showOwnVoteAtTop:

show.showOwnVoteAtTop
"""""""""""""""""""""
.. container:: table-row

   Property
      show.showOwnVoteAtTop
   Data type
      bool
   Default
      1
   Description
      When enabled the row with your own vote is displayed at very top.
      Otherwise it will be displayed below all other votings.

.. _show_showAdministrationBoxAboveVotes:

show.showAdministrationBoxAboveVotes
""""""""""""""""""""""""""""""""""""
.. container:: table-row

   Property
      show.showAdministrationBoxAboveVotes
   Data type
      bool
   Default
      1
   Description
      When enabled the administration box (visible for poll author) is displayed above the voting table.
      Otherwise it will be displayed at very bottom.

.. _form_showReturnLink:

form.showReturnLink
"""""""""""""""""""
.. container:: table-row

   Property
      form.showReturnLink
   Data type
      bool
   Default
      1
   Description
      When enabled, displays a link back on all form pages (new/edit/finish poll).




.. _classes:

classes
"""""""
.. container:: table-row

   Property
      classes
   Data type
      array

   Description
      Some CSS classes can get adjusted with TypoScript:

      ::

          classes {
              table = table
              tableResponsive = table-responsive
              secret = secret
          }


