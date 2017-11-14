;(function(global) {
  "use strict";

  // Load Google Analytics libraries
  GOVUK.Analytics.load();

  // Use document.domain in dev, preview and staging so that tracking works
  // Otherwise explicitly set the domain as www.gov.uk (and not gov.uk).
  var cookieDomain = (document.domain === 'powerofattorneyrefund.service.justice.gov.uk') ? '.powerofattorneyrefund.service.justice.gov.uk' : document.domain;

  // Configure profiles and make interface public
  // for custom dimensions, virtual pageviews and events
  GOVUK.analytics = new GOVUK.Analytics({
    universalId: global.GA_TRACKING_ID  || '',
    cookieDomain: cookieDomain
  });

  // Set custom dimensions before tracking pageviews
  // GOVUK.analytics.setDimension(…)

  // Activate any event plugins eg. print intent, error tracking
  GOVUK.analyticsPlugins.formErrorTracker();
  
  // Track initial pageview
  GOVUK.analytics.trackPageview();
})(window);