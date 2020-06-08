'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle._buildVotingBox = function (checkbox) {
  checkbox.style.display = 'none';

  var currentOption = checkbox.querySelector('option[value="' + checkbox.value + '"]');
  var image = document.createElement('IMG');
  image.src = tx_t3oodle.vars.path + 'Icons/check-' + currentOption.value + '.svg';
  image.alt = checkbox.value;
  image.tabIndex = 2
  image.classList.add('t3oodle-voting-image');

  var event = function (event) {
    if (event.key && event.keyCode !== 32) { // Space
      return;
    }

    var currentOption = checkbox.querySelector('option[value="' + checkbox.value + '"]');
    var nextOption = checkbox.querySelector('option[value="' + checkbox.value + '"] ~ option');
    if (!nextOption) {
      nextOption = currentOption.parentNode.firstChild;
    }

    if (nextOption.value === '-1') {
      nextOption = checkbox.querySelector('option[value="' + nextOption.value + '"] ~ option');
    }

    checkbox.value = nextOption.value;

    image.alt = nextOption.text;
    image.src = tx_t3oodle.vars.path + 'Icons/check-' + nextOption.value + '.svg';
    image.parentNode.classList.remove('voting-status-0');
    image.parentNode.classList.remove('voting-status-1');
    image.parentNode.classList.remove('voting-status-2');
    image.parentNode.classList.add('voting-status-' + nextOption.value);
    tx_t3oodle._calculateVotes();
  }

  image.addEventListener('click', event);
  image.addEventListener('keyup', event);

  checkbox.after(image);
  image.parentNode.classList.add('voting-status-' + currentOption.value);
};

tx_t3oodle._calculateVotes = function () {
  if (document.getElementById('t3oodle-votes-summary')) {
    var summary = document.getElementById('t3oodle-votes-summary');
    var checkboxes = document.querySelectorAll('.t3oodle-voting-checkbox');

    var result = {0: 0, 1: 0, 2: 0};
    for (var i = 0; i < checkboxes.length; i++) {
      var checkbox = checkboxes[i];
      result[checkbox.value]++
    }
    summary.innerHTML = '';
    for (var key in result) {
      var amount = result[key];
      if (key != '0' && amount > 0) {

        var image = document.createElement('IMG');
        image.src = this.vars.path + 'Icons/check-' + key + '.svg';
        image.alt = amount;
        image.classList.add('t3oodle-checkbox-icon');
        summary.appendChild(image);

        var amountSpan = document.createElement('SPAN');
        amountSpan.innerText = amount;
        amountSpan.classList.add('ml-1');
        amountSpan.classList.add('mr-3');
        summary.appendChild(amountSpan);
      }
    }
  }
};

tx_t3oodle['voting-box'] = function (items) {
  for (var i = 0; i < items.length; i++) {
    var checkbox = items[i];
    tx_t3oodle._buildVotingBox(checkbox)
  }
  tx_t3oodle._calculateVotes();
};