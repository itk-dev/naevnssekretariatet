/* global $ */

window.addEventListener('ajaxload', function () {
  const $caseEventSelector = $('#document_createCaseEvent')
  // When case event selector gets selected ...
  $caseEventSelector
    .off('change')
    .on('change', function () {
      console.log($caseEventSelector.val())
      console.log($caseEventSelector.attr('name'))
      // ... retrieve the corresponding form.
      const $form = $(this).closest('form')
      // Simulate form data, but only include the selected case event selector value.
      const data = {}
      data[$caseEventSelector.attr('name')] = $caseEventSelector.val()
      // Submit data via AJAX to the form's action path.
      $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        complete: function (html) {
          // Replace current case event field ...
          $('#document_caseEvent').replaceWith(
            // ... with the returned one from the AJAX response.
            $(html.responseText).find('#document_caseEvent')
          )
        }
      })
    })
})
