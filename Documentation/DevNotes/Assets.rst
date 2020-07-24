.. include:: ../Includes.txt


.. _assets:


Assets
======

t3oodle ships all required CSS and JavaScript code. The markup in templates is based on `Bootstrap CSS Framework <https://getbootstrap.com/>`_ in
version 4.5.

The provided JavaScript is written in vanilla JS. No bootstrap JS or any other library required.


Stylesheets
-----------

All custom styles are located in ``Resources/Public/Stylesheets/t3oodle.css``. This file get included in TypoScript
setup of t3oodle.

Also you can include an optional TypoScript template, which includes a custom build of Bootstrap itself. You don't
need to include this optional styles, when you already use Bootstrap in your frontend.


JavaScripts
-----------

All JavaScript provided by t3oodle are based on vanilla JS. The used date picker is `flatpickr <https://flatpickr.js.org/>`_,
which is also written in vanilla JS. No frameworks are required.

If frameworks are in use, they do not affect t3oodle's functionality.


Asynchronous loading
~~~~~~~~~~~~~~~~~~~~

Main entry point for t3oodle's JavaScript functionality is the file ``Resources/Public/JavaScript/t3oodle.js``.
Here, all other scripts are loaded dynamically, when requested (determined by query selectors on page).
