$(document).ready(function () {
  $('.no-back-link').each(function (idx, elm) {
    new GOVUK.BackLink(elm)
  });

  // Details/summary polyfill from frontend toolkit
  GOVUK.details.init()

  // Where .multiple-choice uses the data-target attribute
  // to toggle hidden content
  var showHideContent = new GOVUK.ShowHideContent()
  showHideContent.init()

  $('.error-summary').focus()
  new GOVUK.ScrollToHash('.error-summary-list')

  new GOVUK.Print('.print')

  new GOVUK.SingleUse('.js-single-use')
})
