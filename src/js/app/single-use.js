;(function (global) {
    'use strict'

    var $ = global.jQuery
    var GOVUK = global.GOVUK || {}

    var SingleUse = function (selector) {
        $(selector).on('submit', this.handleClick)
    }

    SingleUse.prototype.handleClick = function(e) {
        var $form = $(e.target);

        // Disable submit button
        $form.find('input[type="submit"].js-single-use').attr('disabled', 'disabled');
    }

    GOVUK.SingleUse = SingleUse
    global.GOVUK = GOVUK
})(window)