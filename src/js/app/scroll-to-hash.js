;(function (global) {
  'use strict'

  var $ = global.jQuery
  var GOVUK = global.GOVUK || {}

  var ScrollToHash = function (selector) {
    $(selector).on('click', 'a[href*="#"]', this.handleClick)
  }

  ScrollToHash.prototype.handleClick = function(event) {
    var isInPageLink = 
      location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && 
      location.hostname == this.hostname
    
    if (isInPageLink) {
      var target = $(this.hash)
      
      if (target.length) {
        $('html, body').animate({
          scrollTop: target.offset().top - 90
        }, 10)
      }
     }  
  }

  GOVUK.ScrollToHash = ScrollToHash
  global.GOVUK = GOVUK
})(window)