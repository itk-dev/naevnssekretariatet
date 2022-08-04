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
          const $caseDescription = $agendaCaseItem.find(':selected').text()
          const $meetingPoint = $("input[id='agenda_item_agendaItem_meetingPoint']")
          if ($caseDescription.includes('Besigtigelse')) {
            $meetingPoint.val($caseDescription.substring($caseDescription.lastIndexOf('-') + 2))
          } else {
            $meetingPoint.val('')
          }
        })
      }
    }
  })
})
