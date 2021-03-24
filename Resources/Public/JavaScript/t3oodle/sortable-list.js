'use strict';
var tx_t3oodle = tx_t3oodle || {};

tx_t3oodle['sortable-list'] = function (selector) {
    var items = document.querySelectorAll(selector);

    tx_t3oodle.loadScript('libs/html5sortable.js', function () {
        var sort = sortable(items, {
            items: ':not(:last-child)',
            forcePlaceholderSize: true,
            handle: '.handle',
        });
        sort[0].addEventListener('sortupdate', function(e) {
            var i = 1;
            for(var key in e.detail.origin.items) {
                var sortedItem = e.detail.origin.items[key];
                var baum = sortedItem.querySelector('input[name$="[sorting]"]')
                if (baum) {
                    baum.value = i;
                    i = i * 2;
                }
            }
        });
        window.addEventListener('addedNewInputs', function(e) {
            sortable(items)
        });
    });
};

