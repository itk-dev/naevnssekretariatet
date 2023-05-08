/* global $ */

window.addEventListener('ajaxload', function () {
  const $shouldBriefSelector = $('#hearing_post_request_briefExtraParties')
  // When case event selector gets selected ...
  $shouldBriefSelector
    .off('change')
    .on('change', function () {
      const $shouldBriefSelectorhest = $('#hearing_post_request_briefExtraParties')
      // ... retrieve the corresponding form.
      const $form = $(this).closest('form')
      // Simulate form data, but only include the selected should brief selector value.
      const data = {}
      data[$shouldBriefSelectorhest.attr('name')] = $shouldBriefSelectorhest.val()
      const $briefRecipientsElement = $('[id$=hearing_post_request_briefingRecipients]')
      data[$briefRecipientsElement.attr('name')] = ['']
      console.log(data)
      // Submit data via AJAX to the form's action path.
      $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data,
        complete: function (html) {
          const $shouldBriefSelector = $('#hearing_post_request_briefExtraParties')
          console.log($shouldBriefSelector.val())
          // console.log($('#hearing_post_request_briefingTitle').attr('type'))
          // console.log($(html.responseText).find('#hearing_post_request_briefingTitle').attr('type'))
          // console.log(html.responseText)
          // const ids = [
          //   'briefingTitle',
          //   'briefingTemplate',
          //   'briefingRecipients',
          // ]
          // Replace current extra recipients event field ...
          $('#hearing_post_request_briefingTitle').replaceWith(
            // ... with the returned one from the AJAX response.
            $(html.responseText).find('#hearing_post_request_briefingTitle'))

          $('#hearing_post_request_briefingTemplate').replaceWith(
            // ... with the returned one from the AJAX response.
            $(html.responseText).find('#hearing_post_request_briefingTemplate')
          )
          $('#hearing_post_request_briefingRecipients').replaceWith(
            // ... with the returned one from the AJAX response.
            $(html.responseText).find('#hearing_post_request_briefingRecipients')
          )
        }
      })
    })
})
