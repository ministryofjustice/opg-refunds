$(document).ready(function () {
  // Turn off jQuery animation
  jQuery.fx.off = true

  $('h1.no-back-link').each(function (idx, elm) {
    new GOVUK.BackLink(elm);
  });

  // Where .multiple-choice uses the data-target attribute
  // to toggle hidden content
  var showHideContent = new GOVUK.ShowHideContent()
  showHideContent.init()
})