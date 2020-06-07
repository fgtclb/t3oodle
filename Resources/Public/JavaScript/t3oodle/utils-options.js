'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle._buildDynamicOptionInputs = function (newOptionFields, inputClassName, keyupEventCallback) {
  if (!inputClassName) {
    inputClassName = '.t3oodle-new-poll-option';
  }
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
      keyupEventCallback || function () {
        tx_t3oodle._buildDynamicOptionInputs(document.querySelectorAll(inputClassName), inputClassName);
      },
      false
    );
  }
};
