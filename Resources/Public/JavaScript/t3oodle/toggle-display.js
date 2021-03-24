'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle['toggle-display'] = function (selector) {
  var itemsToToggle = document.querySelectorAll(selector);
  for (var i = 0; i < itemsToToggle.length; i++) {
    var itemToToggle = itemsToToggle[i];

    var processItem = function(itemToToggle) {
      var targetItem = document.querySelector(itemToToggle.dataset.toggleDisplay);
      targetItem.addEventListener('change', function (event) {
        var value = event.target.value;
        if (targetItem.type === 'checkbox') {
          value = event.target.checked;
        }

        if (value) {
          itemToToggle.classList.remove('d-none');
        } else {
          itemToToggle.classList.add('d-none');
        }
      });
      targetItem.dispatchEvent(new Event('change'));
    };
    processItem(itemToToggle)
  }
};
