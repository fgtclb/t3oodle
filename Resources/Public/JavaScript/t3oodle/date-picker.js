'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle.vars.datepicker = null;

tx_t3oodle['date-picker'] = function (selector) {
    var item = document.querySelector(selector);

    var target = document.querySelector(item.dataset.datePicker);

    // Datepicker enabled
    var flatpickrStyles = document.createElement('link');
    flatpickrStyles.setAttribute('rel', 'stylesheet');
    flatpickrStyles.setAttribute('type', 'text/css');
    flatpickrStyles.setAttribute('href', tx_t3oodle.vars.path + 'Stylesheets/libs/flatpickr.min.css');
    document.getElementsByTagName('head')[0].appendChild(flatpickrStyles)

    tx_t3oodle.loadScript('libs/flatpickr.js', function () {
        // Get locales from data-locale attribute
        var datepickerLocale = JSON.parse(document.querySelector('.t3oodle-date-picker').dataset.locales);

        var dateFormatNoTime = 'Y-m-d';
        var dateFormat = 'Y-m-d - H:i';
        var savedExtraPart = null;

        function restoreSavedExtraPart() {
            if (savedExtraPart && target.value.match(/ \- /)) {
                target.value = target.value.replace(/(.*?) \- .*/, '$1 - ' + savedExtraPart);
                savedExtraPart = null;
            }
            if (savedExtraPart === false && target.value.match(/ \- /)) {
                target.value = target.value.replace(/(.*?) \- .*/, '$1')
                savedExtraPart = null;
            }
        }

        tx_t3oodle.vars.datepicker = flatpickr(target.parentNode, {
            wrap: true,
            dateFormat: dateFormat,
            minDate: 'today',
            weekNumbers: true,
            enableTime: true,
            locale: datepickerLocale,
            clickOpens: false,
            allowInput: true,
            onChange: restoreSavedExtraPart,
            onReady: restoreSavedExtraPart,
            parseDate: function(dateString) {
                var parsedDateWithoutTime = flatpickr.parseDate(dateString, dateFormatNoTime);
                if (!parsedDateWithoutTime) {
                    return;
                }
                var reverseCheckWithoutTime = flatpickr.formatDate(parsedDateWithoutTime, dateFormatNoTime);
                if (dateString === reverseCheckWithoutTime) {
                    savedExtraPart = false; // when false, do not append empty time (0:00) in restoreSavedExtraPart function
                    return parsedDateWithoutTime;
                }

                var parsedDate = flatpickr.parseDate(dateString, dateFormat);
                var reverseCheck = flatpickr.formatDate(parsedDate, dateFormat);

                if (dateString.match(/ \- /) && dateString !== reverseCheck) {
                    savedExtraPart = dateString.replace(/.*? \- (.*)/, '$1');
                } else {
                    savedExtraPart = null;
                }

                var now = new Date();
                if (now > parsedDate) {
                    return now;
                }
                return parsedDate;
            }
        });
    });
};
