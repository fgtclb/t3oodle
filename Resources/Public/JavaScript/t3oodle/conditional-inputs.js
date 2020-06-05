'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle._applyConditionalInputs = function (checkbox, disabledInputs, isInit) {
  for (var i = 0; i < disabledInputs.length; i++) {
    var disabledInput = disabledInputs[i];
    if (checkbox.checked) {
      disabledInput.readOnly = false;
      if (!isInit) {
        disabledInput.focus();
      }
    } else {
      disabledInput.readOnly = true;
      if (!isInit) {
        if (disabledInput.type === 'number') {
          disabledInput.value = 0;
        } else if (disabledInput.type === 'text') {
          disabledInput.value = '';
        } else {
          disabledInput.value = null;
        }
      }
    }
  }
};

tx_t3oodle['conditional-inputs'] = function (items) {
  for (var i = 0; i < items.length; i++) {
    var disabledInput = items[i];
    var relatedElement = document.querySelector(disabledInput.dataset.bindDisable);
    if (!relatedElement.related) {
      relatedElement.related = [];
    }
    relatedElement.related.push(disabledInput);
    relatedElement.addEventListener('change', function (event) {
      tx_t3oodle._applyConditionalInputs(event.target, event.target.related);
    });
    tx_t3oodle._applyConditionalInputs(relatedElement, relatedElement.related, true);
  }
};

