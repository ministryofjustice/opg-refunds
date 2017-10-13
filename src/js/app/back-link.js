;(function (global) {
  'use strict'

  var BackLink = function (elm) {
    if (global.history && global.history.back && elm) {
      this.$el = $(elm);
      this.setup();
      this.bindEvents();
    }
  };

  BackLink.prototype.setup = function () {
    this.$link = $(
                  '<a class="link-back" href="#">' +
                    'Back' +
                    ' <span class="visuallyhidden"> ' +
                      'to the previous page' +
                    '</span>' +
                  '</a>'
                );
    this.$el.prepend(this.$link);
    this.$el.removeClass('no-back-link');
  };

  BackLink.prototype.bindEvents = function () {
    this.$link.on("click", function(e) {
      e.preventDefault();
      history.back();
      return false;
    });
  };

  GOVUK.BackLink = BackLink
  global.GOVUK = GOVUK
})(window)