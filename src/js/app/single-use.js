;(function (global) {
    'use strict'

    var $ = global.jQuery
    var GOVUK = global.GOVUK || {}

    var SingleUse = function (selector) {
        $(selector).on('click', '.js-single-use', this.handleClick)
    }

    SingleUse.prototype.handleClick = function(event) {
        // Disable submit button
        $(e.target).attr('disabled', 'disabled');
    }

    GOVUK.SingleUse = SingleUse
    global.GOVUK = GOVUK
})(window)