;(function (global) {
    'use strict'

    var $ = global.jQuery
    var GOVUK = global.GOVUK || {}

    var SingleUse = function (selector) {
        $(selector).closest('form').on('submit', this.handleSubmit)
    }

    SingleUse.prototype.handleSubmit = function(e) {
        var $form = $(e.target);

        // Disable submit button
        $form.find('.js-single-use').attr('disabled', 'disabled').attr('aria-disabled', 'true');
    }

    GOVUK.SingleUse = SingleUse
    global.GOVUK = GOVUK
})(window)