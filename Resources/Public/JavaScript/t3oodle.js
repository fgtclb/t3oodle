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
  loadScriptBySelector: function (script, selector) {
    var items = document.querySelectorAll(selector)
    if (items.length > 0) {
      this.loadScript(script + '.js', function () {
        tx_t3oodle[script](items);
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
    this.loadScriptBySelector('voting-box', '.t3oodle-voting-checkbox');
    this.loadScriptBySelector('options-simple', '.t3oodle-new-poll-option');
  }
};
