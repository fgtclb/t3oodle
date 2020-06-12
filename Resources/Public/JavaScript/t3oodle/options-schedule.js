'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle.vars.datepicker = null;
tx_t3oodle.vars.selectedDates = [];
tx_t3oodle.vars.selectedTimes = [];
tx_t3oodle.vars.optionCount = 0;

tx_t3oodle._removeTime = function (node) {
  node.parentNode.querySelector('input').remove();
  node.parentNode.style.display = 'none';
  tx_t3oodle._calcScheduleOptions();
};

tx_t3oodle._calcScheduleOptions = function () {
  var optionsContainer = document.querySelector('.t3oodle-options-per-day');
  var allOptions = optionsContainer.querySelectorAll('.new-option-per-day');

  tx_t3oodle.vars.selectedTimes = [];
  for (var i2 = 0; i2 < allOptions.length; i2++) {
    if (allOptions[i2].value) {
      tx_t3oodle.vars.selectedTimes[i2] = allOptions[i2].value;
    }
  }

  if (tx_t3oodle.vars.selectedTimes.length > 0) {
    tx_t3oodle.vars.optionCount = tx_t3oodle.vars.selectedDates.length * tx_t3oodle.vars.selectedTimes.length;
  } else {
    tx_t3oodle.vars.optionCount = tx_t3oodle.vars.selectedDates.length;
  }

  var item = document.getElementById('schedule-option-amount');
  if (item) {
    item.textContent = tx_t3oodle.vars.optionCount;
  }

  // Reset and build hidden fields
  var container = document.getElementById('scheduled-options');
  container.textContent = '';
  var index = 0;
  for (var i = 0; i < tx_t3oodle.vars.selectedDates.length; i++) {
    var day = tx_t3oodle.vars.datepicker.formatDate(tx_t3oodle.vars.selectedDates[i], 'Y-m-d');

    if (tx_t3oodle.vars.selectedTimes.length > 0) {
      for (var i3 = 0; i3 < tx_t3oodle.vars.selectedTimes.length; i3++) {
        var dayOption = tx_t3oodle.vars.selectedTimes[i3];
        if (dayOption) {
          var value2 = day + ' - ' + dayOption;
          var hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = 'tx_t3oodle_main[poll][options][' + index + '][name]';
          hidden.value = value2;
          container.appendChild(hidden);
          index++;
        }
      }
    } else {
      var hidden2 = document.createElement('input');
      hidden2.type = 'hidden';
      hidden2.name = 'tx_t3oodle_main[poll][options][' + index + '][name]';
      hidden2.value = day;
      container.appendChild(hidden2);
      index++;
    }
  }
};

tx_t3oodle.optionPerDayKeyUpCallback = function () {
  var optionsContainer = document.querySelector('.t3oodle-options-per-day');
  tx_t3oodle._buildDynamicOptionInputs(optionsContainer.querySelectorAll('.new-option-per-day'), '.new-option-per-day', tx_t3oodle.optionPerDayKeyUpCallback);

  tx_t3oodle._calcScheduleOptions();
};

tx_t3oodle['options-schedule'] = function (items) {
  if (items.length !== 1) {
    return;
  }

  // Datepicker enabled
  var flatpickrStyles = document.createElement('link');
  flatpickrStyles.setAttribute('rel', 'stylesheet');
  flatpickrStyles.setAttribute('type', 'text/css');
  flatpickrStyles.setAttribute('href', tx_t3oodle.vars.path + 'Stylesheets/libs/flatpickr.min.css');
  document.getElementsByTagName('head')[0].appendChild(flatpickrStyles)

  tx_t3oodle.loadScript('libs/flatpickr.js', function () {
    var optionsContainer = document.querySelector('.t3oodle-options-per-day');
    var optionsPerDay = optionsContainer.querySelectorAll('.new-option-per-day');

    for (var i = 0; i < optionsPerDay.length; i++) {
      var optionPerDay = optionsPerDay[i];
      optionPerDay.addEventListener('keyup', tx_t3oodle.optionPerDayKeyUpCallback);
    }

    // Get locales from data-locale attribute
    var datepickerLocale = JSON.parse(document.querySelector('.t3oodle-date-picker').dataset.locale);

    tx_t3oodle.vars.datepicker = flatpickr('.t3oodle-date-picker > input', {
      inline: true,
      mode: 'multiple',
      minDate: 'today',
      weekNumbers: true,
      locale: datepickerLocale,
      onChange: function (selectedDates, dateStr, instance) {
        // Liste mit Auswahl
        var ul = instance.element.closest('.row').querySelector('.selected-dates');
        ul.textContent = '';

        var sortedDates = selectedDates.sort(function (a, b) {
          return a - b;
        });
        tx_t3oodle.vars.selectedDates = sortedDates;

        for (var i2 = 0; i2 < sortedDates.length; i2++) {
          var selectedDate = sortedDates[i2];
          var li = document.createElement('li');
          li.textContent = instance.formatDate(selectedDate, 'Y-m-d'); // TODO: Output date
          li.textContent += ' (' + instance.formatDate(selectedDate, 'D') + ')'
          ul.appendChild(li);
        }
        tx_t3oodle._calcScheduleOptions();
      }
    });

    var container = document.getElementById('scheduled-options');
    var existingOptions = container.querySelectorAll('input[name$="[name]"]');

    var indexDate = 0;
    var indexVariation = 0;
    for (var i = 0; i < existingOptions.length; i++) {
      var option = existingOptions[i];

      if (option.value.indexOf(' - ') > -1) {
        var parts = option.value.split(' - ');
        var date = parts[0];
        var variation = parts[1];

        if (tx_t3oodle.vars.selectedDates.indexOf(date) === -1) {
          tx_t3oodle.vars.selectedDates[indexDate] = date;
          indexDate++
        }
        if (tx_t3oodle.vars.selectedTimes.indexOf(variation) === -1) {
          tx_t3oodle.vars.selectedTimes[indexVariation] = variation;
          indexVariation++
        }

      } else {
        tx_t3oodle.vars.selectedDates[indexDate++] = option.value;
      }
    }
    tx_t3oodle.vars.datepicker.setDate(tx_t3oodle.vars.selectedDates, true);

    tx_t3oodle.optionPerDayKeyUpCallback();
  });

};

