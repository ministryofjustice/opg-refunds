// Usage instructions:
// GOVUK.shimLinksWithButtonRole.init();
;(function (global) {
  'use strict'

  var $ = global.jQuery
  var GOVUK = global.GOVUK || {}

  var ScrollToHash = function (selector, opts) {
    $(selector).on('click', 'a[href*="#"]', this.handleClick)
  }

  ScrollToHash.prototype.handleClick = function(event) {
    var isInPageLink = 
      location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && 
      location.hostname == this.hostname
    
    if (isInPageLink) {
      // Figure out element to scroll to
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      // Does a scroll target exist?
      if (target.length) {
        // Only prevent default if animation is actually gonna happen
        event.preventDefault();
        $('html, body').animate({
          scrollTop: target.offset().top - 100
        }, 10000, function() {
          // Callback after animation
          // Must change focus!
          var $target = $(target);
          $target.focus();
          if ($target.is(":focus")) { // Checking if the target was focused
            return false;
          } else {
            $target.attr('tabindex','-1'); // Adding tabindex for elements not focusable
            $target.focus(); // Set focus again
          };
        });
  
        if(history.pushState) {
          history.pushState(null, null, this.hash);
        }
        else {
          location.hash = this.hash;
        }
      }
     }  
  }

  GOVUK.ScrollToHash = ScrollToHash
  
  GOVUK.scrollToHash = {
    init: function (selector) {
      new ScrollToHash(selector)
    }
  }

  // hand back to global
  global.GOVUK = GOVUK
})(window)