/* global describe it expect beforeEach afterEach spyOn */

var $ = window.jQuery
var GOVUK = window.GOVUK || {}

function expectTrackEvent(eventAction, eventLabel) {
  expect(GOVUK.analytics.trackEvent).toHaveBeenCalledWith(
    'Error-field', eventAction, { transport: 'beacon', label: eventLabel}
  )
}

describe('GOVUK.analyticsPlugins.formErrorTracker', function () {
  'use strict'

  var $errorSummary
  var $inputField
  var $inputFieldWithFieldset
  var $radioButtonErrorSummary
  var $radioButtons

  beforeEach(function () {

    $errorSummary = $(
      '<ul class="error-summary-list">' +
        '<li><a href="#id-text-field-1">Text field 1 error message</a></li>' +
        '<li><a href="#id-text-field-2">Text field 2 error message</a></li>' +
    ' </ul>'
    )

    $inputField = $(
      '<div class="form-group form-group-error">' +
        '<label class="form-label" for="id-text-field-1">' +
          '<span class="question-text">Text field 1</span>' +
        '</label>' +
        '<input type="text" id="id-text-field-1" value="">' +
      '</div>' +
      '<div class="form-group form-group-error">' +
        '<label class="form-label" for="id-text-field-2">' +
          '<span class="question-text">Text field 2</span>' +
        '</label>' +
        '<input type="text" id="id-text-field-2" value="">' +
      '</div>'
    )

    $inputFieldWithFieldset = $(
      '<fieldset>' +
        '<legend>LEGEND</legend>' +
        '<div class="form-group form-group-error">' +
          '<label class="form-label" for="id-text-field-1">' +
            '<span class="question-text">Text field 1</span>' +
          '</label>' +
          '<input type="text" id="id-text-field-1" value="">' +
        '</div>' +
        '<div class="form-group form-group-error">' +
          '<label class="form-label" for="id-text-field-2">' +
            '<span class="question-text">Text field 2</span>' +
          '</label>' +
          '<input type="text" id="id-text-field-2" value="">' +
        '</div>' +
      '</fieldset>'
    )

    $radioButtonErrorSummary = $(
      '<ul class="error-summary-list">' +
        '<li><a href="#id-fieldset">Fieldset error message</a></li>' +
    ' </ul>'
    )

    $radioButtons = $(
      '<div class="form-group form-group-error">' +
      '<fieldset>' +
        '<legend id="id-fieldset">' +
          '<span class="visually-hidden question-text">Fieldset question text</span>' +
        '</legend>' +
      '</fieldset>' +
      '</div>'
    )

    beforeEach(function() {
      window.gaConfig = {
        universalId: 'universal_id'
      }
    })

    GOVUK.analytics = {trackEvent: function () {}}
  })

  afterEach(function () {
    GOVUK.analytics.trackEvent.calls.reset()
    delete GOVUK.analytics
  })

  describe('Textbox errors', function() {

    afterEach(function () {
      $errorSummary.remove()
      $inputField.remove()
    })

    it('tracks an error for each error summary link', function() {
      spyOn(GOVUK.analytics, 'trackEvent')
      $('body').append($errorSummary).append($inputField)
      GOVUK.analyticsPlugins.formErrorTracker()
      expectTrackEvent('Text field 1', '#id-text-field-1 - Text field 1 error message')
      expectTrackEvent('Text field 2', '#id-text-field-2 - Text field 2 error message')
    })

  });

  describe('Textbox errors in fieldset', function() {
    
      afterEach(function () {
        $errorSummary.remove()
        $inputFieldWithFieldset.remove()
      })
  
      it('prepends legend to event category', function() {
        spyOn(GOVUK.analytics, 'trackEvent')
        $('body').append($errorSummary).append($inputFieldWithFieldset)
        GOVUK.analyticsPlugins.formErrorTracker()
        expectTrackEvent('LEGEND: Text field 1', '#id-text-field-1 - Text field 1 error message')
        expectTrackEvent('LEGEND: Text field 2', '#id-text-field-2 - Text field 2 error message')
      })
  
    });

  describe('Fieldset level error', function() {
    
    afterEach(function () {
      $radioButtonErrorSummary.remove()
      $radioButtons.remove()
    })

    it('tracks an error for each error summary link', function() {
      spyOn(GOVUK.analytics, 'trackEvent')
      $('body').append($radioButtonErrorSummary).append($radioButtons)
      GOVUK.analyticsPlugins.formErrorTracker()
      expectTrackEvent('Fieldset question text', '#id-fieldset - Fieldset error message')
    })

  });

})