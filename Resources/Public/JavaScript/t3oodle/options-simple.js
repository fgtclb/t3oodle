'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle._buildDynamicOptionInputs = function (newOptionFields) {
  var allOptionsFilled = true;
  var lastItem = null;
  for (var i = 0; i < newOptionFields.length; i++) {
    var value = newOptionFields[i].value;

    if (!value || value.trim() === '' || value.trim().length === 0) {
      allOptionsFilled = false;
    }
    if (newOptionFields.length - 1 === i) {
      lastItem = newOptionFields[i];
    }
  }
  // Create new input box
  if (allOptionsFilled) {
    this.vars.newOptionIndex++
    var clone = lastItem.parentNode.cloneNode(true);
    var identityHiddenField = clone.querySelector('input[name*="__identity"]');
    if (identityHiddenField) {
      identityHiddenField.remove();
    }
    var errorMessagesNode = clone.querySelector('ul');
    if (errorMessagesNode) {
      errorMessagesNode.remove();
    }
    var cloneInputs = clone.querySelectorAll('input');
    for (var i = 0; i < cloneInputs.length; i++) {
      var cloneInput = cloneInputs[i];
      var updatedInputName = cloneInput.name.replace(
        /(.*\[options\]\[).*?(\]\[(name|markToDelete|__identity)\].*)/g,
        '$1' + this.vars.newOptionIndex + '$2'
      );
      cloneInput.name = updatedInputName;
      cloneInput.value = '';
      cloneInput.classList.remove('f3-form-error');
    }
    clone.style.display = 'list-item';
    lastItem.parentNode.after(clone);
    clone.addEventListener(
      'keyup',
      function () {
        tx_t3oodle._buildDynamicOptionInputs(document.querySelectorAll('.t3oodle-new-poll-option'));
      },
      false
    );
  }
};

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
