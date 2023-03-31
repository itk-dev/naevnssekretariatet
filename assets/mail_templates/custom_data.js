/* global $ */

window.addEventListener('ajaxload', function () {
  const $template = $("[id$='template']")
  const ids = [
    'title',
    'template',
    'recipient'
  ]

  $template
    .off('change')
    .on('change', function () {
      const $form = $(this).closest('form')
      const data = {}

      ids.forEach(id => {
        const $element = $('[id$=' + id + ']')
        data[$element.attr('name')] = $element.val()
      })
      // Submit data via AJAX to the form's action path.
      $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data,
        success: function (html) {
          $("[id$='customData']").replaceWith(
            $(html).find("[id$='customData']")
          )
        }
      })
    })
})
