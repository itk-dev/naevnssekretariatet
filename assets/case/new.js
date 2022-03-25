/* global $, Event */

// On load trigger change once to preselect municipality
window.addEventListener('load', function () {
  const $municipality = $('#case_entity_municipality')
  $municipality.trigger('change')
})

window.addEventListener('ajaxload', function () {
  const $municipality = $('#case_entity_municipality')

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
          $('#case_entity_board').replaceWith(
            $(html).find('#case_entity_board')
          )

          $('#case_entity_caseEntity').replaceWith(
            $(html).find('#case_entity_caseEntity')
          )

          window.dispatchEvent(new Event('ajaxload'))
        }
      })
    })

  const $board = $('#case_entity_board')
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
          $('#case_entity_caseEntity').replaceWith(
            $(html).find('#case_entity_caseEntity')
          )
          window.dispatchEvent(new Event('ajaxload'))
        }
      })
    })

  const $copyAddressButton = $("[id*='copyAddress']")

  $copyAddressButton.on('click', function () {
    const $leaseElements = $("input[id*='leaseAddress']")
    // We should copy bringer address into lease address
    for (const item of $leaseElements) {
      // Find value from bringerAddress
      const currentBringerItem = $('#' + item.id.replace('leaseAddress', 'bringerAddress'))
      item.value = currentBringerItem.val()
    }
  })
})
