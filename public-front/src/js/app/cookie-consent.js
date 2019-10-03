;(function(root) {
    'use strict';

    window.GOVUK = window.GOVUK || {}

    var CookieConsent = function() {
        if (!this.isInCookiesPage() && !this.isInIframe() && "true" !== window.GOVUK.cookie('seen_cookie_message')) {
            this.displayCookieMessage(true);
            window.GOVUK.cookie('cookie_policy') || window.GOVUK.setDefaultConsentCookie()
        }

        this.enableAllCookies = this.enableAllCookies.bind(this);
        var acceptButton = document.querySelector('.global-cookie-message__button_accept');
        acceptButton.addEventListener('click', this.enableAllCookies);
    }

    CookieConsent.prototype.displayCookieMessage = function(show) {
        var message = document.getElementById('global-cookie-message');

        if (show) {
            message.style.display = 'block';
        } else {
            message.removeAttribute('style');
        }
    };

    CookieConsent.prototype.enableAllCookies = function(evt) {
        window.GOVUK.approveAllCookieTypes();
        window.GOVUK.cookie('seen_cookie_message', "true");
        this.displayCookieMessage(false);

        // enable analytics and fire off a pageview
        window.GOVUK.analyticsSetup(window)
    }

    CookieConsent.prototype.isInCookiesPage = function() {
        return '/cookies' === window.location.pathname
    };

    CookieConsent.prototype.isInIframe = function () {
        return window.parent && window.location !== window.parent.location
    },

    window.GOVUK.CookieConsent = CookieConsent
})(window)