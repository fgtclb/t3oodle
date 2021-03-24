'use strict';
var tx_t3oodle = {
  vars: {
    path: '',
    newOptionIndex: 2
  },
  loadScript: function (name, callback) {
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.async = true;
    if (callback) {
      script.onload = callback;
    }
    script.src = this.vars.path + 'JavaScript/t3oodle/' + name;
    document.getElementsByTagName('head')[0].appendChild(script);
  },
  loadScriptBySelector: function (script, selector, callback) {
    var items = document.querySelectorAll(selector);
    if (items.length > 0) {
      this.loadScript(script + '.js', callback || function () {
        tx_t3oodle[script](selector);
      });
    }
  },
  init: function (scriptNode) {
    if (!scriptNode.dataset.path) {
      console.error('Required data tag "data-path" missing at t3oodle script tag!');
    }
    this.vars.path = scriptNode.dataset.path;

    if (document.getElementById('t3oodleLastOptionIndex')) {
      this.vars.newOptionIndex = document.getElementById('t3oodleLastOptionIndex').value;
    }

    // Init required scripts
    this.loadScriptBySelector('confirmation', '*[data-confirm]');
    this.loadScriptBySelector('remember', '*[data-remember]');
    this.loadScriptBySelector('conditional-inputs', 'input[data-bind-disable]');
    this.loadScriptBySelector('force-checked', 'input[data-force-checked]');
    this.loadScriptBySelector('voting-box', '.t3oodle-voting-checkbox');
    this.loadScriptBySelector('toggle-display', '*[data-toggle-display]');
    this.loadScriptBySelector('utils-options', '.t3oodle-new-poll-option, .t3oodle-options-per-day', function () {
      tx_t3oodle.loadScriptBySelector('options-simple', '.t3oodle-new-poll-option');
      tx_t3oodle.loadScriptBySelector('options-schedule', '.t3oodle-options-per-day');
    });
    this.loadScriptBySelector('date-picker', '*[data-date-picker]');
    this.loadScriptBySelector('sortable-list', '*[data-sortable-list]');
  }
};
