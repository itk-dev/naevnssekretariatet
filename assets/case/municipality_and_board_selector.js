/* global $, Event */

// On load trigger change once to preselect municipality
window.addEventListener('load', function () {
  const $municipality = $('#new_case_municipality_and_board_municipality')
  $municipality.trigger('change')
})

window.addEventListener('ajaxload', function () {
  const $municipality = $('#new_case_municipality_and_board_municipality')

  $municipality
    .off('change')
    .on('change', function () {
      const $form = $(this).closest('form')
      const data = {}
      data[$municipality.attr('name')] = $municipality.val()
      // Submit data via AJAX to the form's action path.
      $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
          $('#new_case_municipality_and_board_board').replaceWith(
            $(html).find('#new_case_municipality_and_board_board')
          )

          window.dispatchEvent(new Event('ajaxload'))
        }
      })
    })

  const $board = $('#new_case_municipality_and_board_board')
  $board
    .off('change')
    .on('change', function () {
      const $form = $(this).closest('form')
      const data = {}
      data[$board.attr('name')] = $board.val()
      data[$municipality.attr('name')] = $municipality.val()
      // Submit data via AJAX to the form's action path.
      $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
          window.dispatchEvent(new Event('ajaxload'))
        }
      })
    })
})
