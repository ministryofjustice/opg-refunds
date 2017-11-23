;GOVUK.analyticsSetup = function(global) {
  "use strict";

  var $ = global.jQuery
  var GOVUK = global.GOVUK || {}
  var gaConfig = global.gaConfig || {}

  // Load Google Analytics libraries
  GOVUK.Analytics.load();

  // Use document.domain in dev, preview and staging so that tracking works
  // Otherwise explicitly set the domain as powerofattorneyrefund.service.justice.gov.uk.
  var cookieDomain = (document.domain === 'powerofattorneyrefund.service.justice.gov.uk') ? '.powerofattorneyrefund.service.justice.gov.uk' : document.domain;

  // Configure profiles and make interface public
  // for custom dimensions, virtual pageviews and events
  GOVUK.analytics = new GOVUK.Analytics({
    universalId: gaConfig.universalId  || '',
    cookieDomain: cookieDomain
  });

  // Set custom dimensions before tracking pageviews
  if (typeof gaConfig.sessionId !== 'undefined') {
    GOVUK.analytics.setDimension(gaConfig.dimensions.ANONYMOUS_SESSION_ID, gaConfig.sessionId)
  }

  if (typeof gaConfig.releaseTag !== 'undefined') {
    GOVUK.analytics.setDimension(gaConfig.dimensions.RELEASE_TAG, gaConfig.releaseTag)
  }
  
  // Activate any event plugins eg. print intent, error tracking
  GOVUK.analyticsPlugins.formErrorTracker();
  
  // Track initial pageview
  if (typeof GOVUK.pageviewOptions !== 'undefined') {
    GOVUK.analytics.trackPageview(null, null, GOVUK.pageviewOptions);
  }
  else {
    GOVUK.analytics.trackPageview();
  }

};

GOVUK.analyticsSetup(window)