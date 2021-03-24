'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle._applyConditionalInputs = function (checkbox, disabledInputs, isInit) {
  for (var i = 0; i < disabledInputs.length; i++) {
    var disabledInput = disabledInputs[i];
    if (checkbox.checked) {
      disabledInput.readOnly = false;
      if (!isInit && (!disabledInput.previousElementSibling || !disabledInput.previousElementSibling.type)) {
        disabledInput.focus();
      }
    } else {
      disabledInput.readOnly = true;
      if (!isInit) {
        disabledInput.classList.remove('f3-form-error');
        var errorList = disabledInput.parentNode.querySelectorAll('.errors');
        if (errorList.length > 0) {
          for (var e = 0; e < errorList.length; e++) {
            errorList[e].style.display = 'none';
          }
        }
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

tx_t3oodle['conditional-inputs'] = function (selector) {
  var items = document.querySelectorAll(selector);
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

