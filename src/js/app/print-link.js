;(function (global) {
    'use strict'

    var $ = global.jQuery
    var GOVUK = global.GOVUK || {}

    var Print = function (selector) {
        $(selector).on('click', 'a[href*="#"]', this.handleClick)
    }

    Print.prototype.handleClick = function(event) {
        var isInPageLink =
            location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') &&
            location.hostname == this.hostname

        if (isInPageLink) {
            GOVUK.analytics.trackEvent('Print-page', 'User requested to print page', { transport: 'beacon' })

            window.print()
            return false
        }
    }

    GOVUK.Print = Print
    global.GOVUK = GOVUK
})(window)