.. include:: ../Includes.txt


.. _routingEnhancer:


Routing enhancer
================

t3oodle ships routing enhancer configurations, you can include or copy to your site configuration.

To import the existing configuration, add this to the top of your site's configuration ``config.yaml``:

::

    imports:
      - { resource: 'EXT:t3oodle/Configuration/Routes/Poll.yaml' }


Supported routes
----------------

The following routes are defined by t3oodle, you can define your own, if you want to:

https://domain.com/path/to/page

- **List Action:** ``/`` or ``/page/2`` or ``/seite/2``
- **New Action:** ``/create`` or ``/create-schedule`` or ``/erstellen`` or ``/erstellen-terminfindung``
- **Show Action:** ``/poll/{poll-slug}`` or ``/umfrage/{poll-slug}``
- **Edit Action:** ``/poll/{poll-slug}/edit`` or ``/umfrage/{poll-slug}/bearbeiten``
- **Finish Action:** ``/poll/{poll-slug}/finish`` or ``/umfrage/{poll-slug}/fertigstellen``

All static parts of routes are implemented by ``LocaleModifier`` aspect, to provide translations.

.. note::
   Caution when editing the site configuration ``config.yaml`` proper indention is required!

