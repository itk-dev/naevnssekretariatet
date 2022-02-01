/* global $, Event */

const $municipality = $('#case_entity_municipality')
$municipality.change(function () {
  const $form = $(this).closest('form')
  const data = {}
  data[$municipality.attr('name')] = $municipality.val()
  // Submit data via AJAX to the form's action path.
  $.ajax({
    url: $form.attr('action'),
    type: $form.attr('method'),
    data: data,
    success: function (html) {
      $('#case_entity_board').replaceWith(
        $(html).find('#case_entity_board')
      )
      window.dispatchEvent(new Event('ajaxload'))
    }
  })
})
//
// const $board = $('#case_entity_board')
// $board.change(function () {
//   const $form = $(this).closest('form')
//   const data = {}
//   data[$board.attr('name')] = $board.val()
//   // Submit data via AJAX to the form's action path.
//   $.ajax({
//     url: $form.attr('action'),
//     type: $form.attr('method'),
//     data: data,
//     success: function (html) {
//       $('#case_entity_caseEntity').replaceWith(
//         $(html).find('#case_entity_caseEntity')
//       )
//       window.dispatchEvent(new Event('ajaxload'))
//     }
//   })
// })
