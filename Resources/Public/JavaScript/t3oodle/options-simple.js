'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle._removeOption = function (node) {
  var input = node.parentNode.querySelector('input[name*="markToDelete"]');
  if (input) {
    input.value = '1';
  } else {
    node.parentNode.querySelector('input').remove();
  }
  node.parentNode.style.display = 'none';
};

tx_t3oodle['options-simple'] = function (items) {
  for (var i = 0; i < items.length; i++) {
    items[i].addEventListener(
      'keyup',
      function () {
        tx_t3oodle._buildDynamicOptionInputs(document.querySelectorAll('.t3oodle-new-poll-option'));
      },
      false
    );
  }
  if (items.length > 0) {
    tx_t3oodle._buildDynamicOptionInputs(items);
  }
};
