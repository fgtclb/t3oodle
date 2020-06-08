'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle.remember = function (items) {
  if (localStorage) {
    for (var i = 0; i < items.length; i++) {
      var rememberInput = items[i];
      if (!rememberInput.value) {
        var key = 't3oodle-remember-' + rememberInput.dataset.remember;
        var value = localStorage.getItem(key);
        if (value) {
          rememberInput.value = value;
        }
      }

      if (rememberInput.value && document.cookie && document.cookie.indexOf('tx_t3oodle_userIdent=') > -1) {
        rememberInput.readOnly = true;
        rememberInput.classList.add('disabled');
      }

      rememberInput.addEventListener('change', function (event) {
        var key = 't3oodle-remember-' + event.target.dataset.remember;
        if (event.target.value) {
          localStorage.setItem(key, event.target.value)
        } else {
          localStorage.removeItem(key)
        }
      });
    }
  }
};
