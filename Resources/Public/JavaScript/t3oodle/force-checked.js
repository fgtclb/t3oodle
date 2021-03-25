'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle._applyForceChecked = function (masterCheckbox, isInit) {
  var slaveCheckboxes = document.querySelectorAll(masterCheckbox.dataset.forceChecked);
  for (var i = 0; i < slaveCheckboxes.length; i++) {
    var slaveCheckbox = slaveCheckboxes[i];
    if (masterCheckbox.checked) {
      slaveCheckbox.checked = true;
    }
  }
};

tx_t3oodle['force-checked'] = function (selector) {
  let items = document.querySelectorAll(selector);
  for (let i = 0; i < items.length; i++) {
    let masterCheckbox = items[i];
    masterCheckbox.addEventListener('change', function (event) {
      tx_t3oodle._applyForceChecked(event.target);
    });
    tx_t3oodle._applyForceChecked(masterCheckbox, true);

    let slaveCheckboxes = document.querySelectorAll(masterCheckbox.dataset.forceChecked);
    for (let i2 = 0; i2 < slaveCheckboxes.length; i2++) {
      let slaveCheckbox = slaveCheckboxes[i2];
      slaveCheckbox.addEventListener('change', function (event) {
        if (!event.target.checked && masterCheckbox.checked) {
          masterCheckbox.checked = false;
        }
      });
    }
  }
};
