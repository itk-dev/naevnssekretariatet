/* global $ */

const $type = $('#agenda_item_type')
$type.on('change', function () {
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

      if ($type.val() === 'App\\Entity\\AgendaCaseItem') {
        const $agendaCaseItem = $('#agenda_item_agendaItem_caseEntity')
        $agendaCaseItem.on('change', function () {
          const $meetingPoint = $("input[id='agenda_item_agendaItem_meetingPoint']")
          $.ajax({
            url: '/case/get/inspection-address',
            type: 'POST',
            data: {
              identifier: $agendaCaseItem.val()
            },
            success: function (response) {
              $meetingPoint.val(response.address)
            }
          })
        })
      }
    }
  })
})
