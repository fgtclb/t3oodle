'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle.confirmation = function (selector) {
  var items = document.querySelectorAll(selector);
  for (var i = 0; i < items.length; i++) {
    var link = items[i];
    link.addEventListener('click', function (event) {
      if (!confirm(this.dataset.confirm)) {
        event.preventDefault();
      }
    });
  }
};
