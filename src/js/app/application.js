$(document).ready(function () {
  $('.no-back-link').each(function (idx, elm) {
    new GOVUK.BackLink(elm);
  });

  // Where .multiple-choice uses the data-target attribute
  // to toggle hidden content
  var showHideContent = new GOVUK.ShowHideContent()
  showHideContent.init()

  $('.error-summary').focus()
  new GOVUK.ScrollToHash('.error-summary-list')
})
