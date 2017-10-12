;(function (global) {
  'use strict'

  var BackLink = function (elm) {
    if (elm) {
      this.$header = $(elm);
      this.setup();
      this.bindEvents();
    }
  };

  BackLink.prototype.setup = function () {
    this.$link = $(
                  '<a class="link-back" href="#">' +
                    'Back' +
                    ' <span class="visuallyhidden"> ' +
                      'to the previous question' +
                    '</span>' +
                  '</a>'
                );
    this.$header.before(this.$link);
    this.$header.removeClass('no-back-link');
  };

  BackLink.prototype.bindEvents = function () {
    this.$link.on("click", function(e) {
      e.preventDefault();
      console.log("BACK");
      root.history.back();
      return false;
    });
  };

  GOVUK.BackLink = BackLink
  global.GOVUK = GOVUK
})(window)