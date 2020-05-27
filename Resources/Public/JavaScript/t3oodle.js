var tx_t3oodle = {};
tx_t3oodle.globals = {newOptionIndex: 2 };
tx_t3oodle.dynamicOptionInputs = function(newOptionFields) {
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
    tx_t3oodle.globals.newOptionIndex++
    var clone = lastItem.parentNode.cloneNode(true);
    var identityHiddenField = clone.querySelector('input[name*="__identity"]');
    if (identityHiddenField) {
      identityHiddenField.remove();
    }
    var cloneInputs = clone.querySelectorAll('input');
    for (var i = 0; i < cloneInputs.length; i++) {
      var cloneInput = cloneInputs[i];
      var updatedInputName = cloneInput.name.replace(
        /(.*\[options\]\[).*?(\]\[(name|markToDelete|__identity)\].*)/g,
        '$1' + tx_t3oodle.globals.newOptionIndex + '$2'
      );
      cloneInput.name = updatedInputName;
      cloneInput.value = '';
      cloneInput.classList.remove('f3-form-error');
    }
    clone.style.display = 'list-item';
    lastItem.parentNode.after(clone);
    clone.addEventListener(
      'keyup',
      function() {
        tx_t3oodle.dynamicOptionInputs(newOptionFields);
      },
      false
    );
  }
};

tx_t3oodle.removeOption = function(node) {
  var input = node.parentNode.querySelector('input[name*="markToDelete"]');
  if (input) {
    input.value = '1';
  } else {
    node.parentNode.querySelector('input').remove();
  }
  node.parentNode.style.display = 'none';
};

tx_t3oodle.conditionalInputs = function(checkbox, disabledInputs, isInit) {
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

tx_t3oodle.getPath = function() {
  var path = '/typo3conf/ext/t3oodle/Resources/Public/';
  if (document.getElementById('t3oodleResourcePath')) {
    path = document.getElementById('t3oodleResourcePath').value;
  }
  return path;
};

tx_t3oodle.votingBox = function(checkbox) {
  checkbox.style.display = 'none';

  var path = tx_t3oodle.getPath();
  var currentOption = checkbox.querySelector('option[value="' + checkbox.value + '"]');
  var image = document.createElement('IMG');
  image.src = path + 'Icons/check-' + currentOption.value + '.svg';
  image.alt = checkbox.value;
  image.tabIndex = 2
  image.classList.add('t3oodle-voting-image');


  var event = function(event){
    if (event.key && event.keyCode !== 32) { // Space
      return;
    }

    var currentOption = checkbox.querySelector('option[value="' + checkbox.value + '"]');
    var nextOption = checkbox.querySelector('option[value="' + checkbox.value + '"] ~ option');
    if (!nextOption) {
      nextOption = currentOption.parentNode.firstChild;
    }
    checkbox.value = nextOption.value;

    image.alt = nextOption.text;
    image.src = path + 'Icons/check-' + nextOption.value + '.svg';
    tx_t3oodle.calculateVotes();
  }

  image.addEventListener('click', event);
  image.addEventListener('keyup', event);

  checkbox.after(image);
};

tx_t3oodle.calculateVotes = function() {
  if (document.getElementById('t3oodle-votes-summary')) {
    var summary = document.getElementById('t3oodle-votes-summary');
    var checkboxes = document.querySelectorAll('.t3oodle-voting-checkbox');

    var result = {0: 0, 1: 0, 2: 0};
    for (var i = 0; i < checkboxes.length; i++) {
      var checkbox = checkboxes[i];
      result[checkbox.value]++
    }
    var path = tx_t3oodle.getPath();
    summary.innerHTML = '';
    for (var key in result) {
      var amount = result[key];
      if (key != '0' && amount > 0) {

        var image = document.createElement('IMG');
        image.src = path + 'Icons/check-' + key + '.svg';
        image.alt = amount;
        image.tabIndex = 2
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

window.onload = function() {
  if (document.getElementById('t3oodleLastOptionIndex')) {
    tx_t3oodle.globals.newOptionIndex = document.getElementById('t3oodleLastOptionIndex').value;
  }

  // Init dynamic options
  var newOptionFields = document.getElementsByClassName('t3oodle-new-poll-option');
  for (var i = 0; i < newOptionFields.length; i++) {
    newOptionFields[i].addEventListener(
      'keyup',
      function() {
        tx_t3oodle.dynamicOptionInputs(newOptionFields);
      },
      false
    );
  };
  if (newOptionFields.length > 0) {
    tx_t3oodle.dynamicOptionInputs(newOptionFields);
  }

  // Init conditional input fields
  var disabledInputFields = document.querySelectorAll('input[data-bind-disable]');
  for (var i = 0; i < disabledInputFields.length; i++) {
    var disabledInput = disabledInputFields[i];
    var relatedElement = document.querySelector(disabledInput.dataset.bindDisable);

    if (!relatedElement.related) {
      relatedElement.related = [];
    }
    relatedElement.related.push(disabledInput);

    relatedElement.addEventListener('change', function(event) {
      tx_t3oodle.conditionalInputs(event.target, event.target.related);
    });

    tx_t3oodle.conditionalInputs(relatedElement, relatedElement.related, true);
  }


  // Init Voting Box
  var checkboxes = document.querySelectorAll('.t3oodle-voting-checkbox');
  for (var i = 0; i < checkboxes.length; i++) {
    var checkbox = checkboxes[i];
    tx_t3oodle.votingBox(checkbox)
  }
  tx_t3oodle.calculateVotes();


  // Init confirmation boxes
  var links = document.querySelectorAll('*[data-confirm]');
  for (var i = 0; i < links.length; i++) {
    var link = links[i];
    link.addEventListener('click', function(event){
      if (!confirm(this.dataset.confirm)) {
        event.preventDefault();
      }
    });
  }
};
