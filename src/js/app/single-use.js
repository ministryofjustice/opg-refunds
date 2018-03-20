;(function (global) {
    'use strict'

    var $ = global.jQuery
    var GOVUK = global.GOVUK || {}

    var SingleUse = function (selector) {
        $(selector).on('click', this.handleClick)
        $(selector).closest('form').on('submit', this.handleSubmit)
    }

    SingleUse.prototype.handleClick = function(e) {
        var $button = $(e.target);

        // Disable submit button
        $button.attr('disabled', 'disabled').attr('aria-disabled', 'true');

        // Ensure the form is still submitted
        $button.closest('form').submit()
    }

    SingleUse.prototype.handleSubmit = function(e) {
        var $form = $(e.target);

        // Disable submit button
        $form.find('.js-single-use').attr('aria-disabled', 'true');
    }

    GOVUK.SingleUse = SingleUse
    global.GOVUK = GOVUK
})(window)