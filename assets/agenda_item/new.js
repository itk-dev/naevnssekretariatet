/* global $ */

const $type = $('#agenda_item_type')
$type.change(function () {
  const $form = $(this).closest('form')
  const data = {}
  data[$type.attr('name')] = $type.val()
  // Submit data via AJAX to the form's action path.
  $.ajax({
    url: $form.attr('action'),
    type: $form.attr('method'),
    data: data,
    success: function (html) {
      $('#agenda_item_agendaItem').replaceWith(
        $(html).find('#agenda_item_agendaItem')
      )
    }
  })
})
