.. include:: ../Includes.txt


.. _templatesPartials:


Templates & Partials
====================

t3oodle output is structured to several templates and partials (using sections).


Templates
---------

- **List** Displays a list of polls.
- **Show** Displays details of a single poll. Here, participants can vote.
- **New** Form to create a new poll.
- **Edit** Form to update an existing poll.
- **Finish** Displays form to select final option, to finish poll.

All other actions (create, update, delete) redirect to list or show action, by default.


Partials
--------

- **FormFieldErrors.html** Inline validation/error messages for form inputs.
- **Poll/**

  - **New/**, **Edit/** and **Finish/** Each action has dedicated partials

    - **Header.html**
    - **Form.html**

  - **Show/**

    - **Header.html**
    - **Info.html**
    - **NoticeExpireDate.html**
    - **NoticeSecretVoting.html**
    - **Administration.html** Box with poll actions. Just visible for the poll author and administrators.

      - **Buttons.html**
      - **LabelPublishStatus.html**
      - **NoticeVotingDisabled.html**
      - **WhoYouAre.html**

  - **AuthorInfo.html** Username/Mail of poll author.
  - **FormFields.html** All form fields a poll has.

    - **General.html**
    - **Author.html**
    - **Settings.html**
    - **Options/**

      - **Header.html** Used in both types
      - **Type/**

        - **Simple.html** Options section for simple poll
        - **Schedule.html** Options section (with date picker) for scheduled poll

  - **List/**

    - **Header.html**
    - **Totals.html**
    - **Table.html**

      - **Row.html**

    - **NewButton.html**

  - **Voting.html** Voting entrance point. Contains poll notes, the vote form and table.

    - **Box.html** A single checkbox in voting row.
    - **ParticipantInfo.html** Username/Mail of participant.
    - **Row.html** A single row in voting table.
    - **Summary.html** Shows submit button for voting or displays final option, when poll is finished.

      - **PollFinished.html**
      - **PollNotFinished.html**

    - **Table.html**

      - **Head.html**
      - **YourVote.html**

