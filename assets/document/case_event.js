/* global $ */

window.addEventListener('ajaxload', function () {
  const $caseEventSelector = $('#document_createCaseEvent')
  const $documentName = $('#document_documentName')
  const $documentType = $('#document_type')
  // When case event selector gets selected ...
  $caseEventSelector
    .off('change')
    .on('change', function () {
      // ... retrieve the corresponding form.
      const $form = $(this).closest('form')
      // Simulate form data, but only include the selected case event selector value.
      const data = {}
      data[$caseEventSelector.attr('name')] = $caseEventSelector.val()
      data[$documentName.attr('name')] = $documentName.val()
      data[$documentType.attr('name')] = $documentType.val()
      // Submit data via AJAX to the form's action path.
      $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data,
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
