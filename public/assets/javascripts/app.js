!function(t){"use strict";var n=function(n){t.history&&t.history.back&&n&&(this.$el=$(n),this.setup(),this.bindEvents())};n.prototype.setup=function(){this.$link=$('<a class="link-back" href="#">Back <span class="visuallyhidden"> to the previous page</span></a>'),this.$el.prepend(this.$link),this.$el.removeClass("no-back-link")},n.prototype.bindEvents=function(){this.$link.on("click",function(t){return t.preventDefault(),history.back(),!1})},GOVUK.BackLink=n,t.GOVUK=GOVUK}(window),function(t){"use strict";var n=t.jQuery,i=t.GOVUK||{},e=function(t){n(t).on("click",'a[href*="#"]',this.handleClick)};e.prototype.handleClick=function(t){if(location.pathname.replace(/^\//,"")==this.pathname.replace(/^\//,"")&&location.hostname==this.hostname){var i=n(this.hash);i.length&&n("html, body").animate({scrollTop:i.offset().top-90},10)}},i.ScrollToHash=e,t.GOVUK=i}(window),function(t){"use strict";var n=t.jQuery,i=t.GOVUK||{},e=function(t){n(t).on("click",'a[href*="#"]',this.handleClick)};e.prototype.handleClick=function(t){if(location.pathname.replace(/^\//,"")==this.pathname.replace(/^\//,"")&&location.hostname==this.hostname)return i.analytics.trackEvent("Print-page","User requested to print page",{transport:"beacon"}),window.print(),!1},i.Print=e,t.GOVUK=i}(window),function(t){"use strict";var n=t.jQuery,i=t.GOVUK||{},e=function(t){n(t).closest("form").on("submit",this.handleSubmit)};e.prototype.handleSubmit=function(t){n(t.target).find(".js-single-use").attr("disabled","disabled").attr("aria-disabled","true")},i.SingleUse=e,t.GOVUK=i}(window),$(document).ready(function(){$(".no-back-link").each(function(t,n){new GOVUK.BackLink(n)}),GOVUK.details.init(),(new GOVUK.ShowHideContent).init(),$(".error-summary").focus(),new GOVUK.ScrollToHash(".error-summary-list"),new GOVUK.Print(".print"),new GOVUK.SingleUse(".js-single-use")});
//# sourceMappingURL=app.js.map
