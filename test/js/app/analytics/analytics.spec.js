/* global describe it expect beforeEach afterEach spyOn */

var $ = window.jQuery
var GOVUK = window.GOVUK || {}


describe('Track page view', function () {
  'use strict'

  beforeEach(function() {
    window.gaConfig = {
      universalId: 'universal_id',
      dimensions: {
          ANONYMOUS_SESSION_ID: '1'
      },
      sessionId: {}
    }
  })

  afterEach(function () {
    GOVUK.Analytics.prototype.trackPageview.calls.reset()
    delete GOVUK.analytics
    delete GOVUK.pageviewOptions
  })

  describe('when pageviewOptions variable is present in global scope', function() {
    
    it('should call trackPageview with options', function() {
      GOVUK.pageviewOptions = { sessionControl: 'end' }
      spyOn(GOVUK.Analytics.prototype, 'trackPageview')  
      GOVUK.analyticsSetup(window)
      expect(GOVUK.Analytics.prototype.trackPageview).toHaveBeenCalledWith(null, null, {
        sessionControl: 'end'
      })
    })

  });
    
  describe('when pageviewOptions variable is not present in global scope', function() {
    
    it('should call track page view without sessionControl end', function() {
      spyOn(GOVUK.Analytics.prototype, 'trackPageview')
      
      GOVUK.analyticsSetup(window)
      expect(GOVUK.Analytics.prototype.trackPageview).toHaveBeenCalledWith()
    })

  });

})