/* global $ */

$(function () {
  const $identificationLookupButton = $('.lookup-identifier')

  $identificationLookupButton.on('click', function () {
    const $identificationType = $('#Party_identification_type')
    const $identificationIdentifier = $('#Party_identification_identifier')

    $.ajax({
      url: '/case/new/apply-identifier-data',
      data: {
        type: $identificationType.val(),
        identifier: $identificationIdentifier.val()
      },
      success: function (response) {
        if ($.isEmptyObject(response)) {
          // No data returned, remove values for now
          $('#Party_name').val('')
          $('#Party_address_street').val('')
          $('#Party_address_number').val('')
          $('#Party_address_floor').val('')
          $('#Party_address_side').val('')
          $('#Party_address_postalCode').val('')
          $('#Party_address_city').val('')
          $('#Party_isUnderAddressProtection').prop('checked', false)

          // Indicate that identifier was not found
          $($identificationLookupButton).removeClass(function () {
            const regExp = /btn-[^\s]*/
            const regExpResult = regExp.exec($(this).attr('class'))

            return regExpResult[0]
          }).addClass('btn-danger')
        } else {
          // Insert values into correct html elements
          $('#Party_name').val(response.name)
          $('#Party_address_street').val(response.street)
          $('#Party_address_number').val(response.number)
          $('#Party_address_floor').val(response.floor)
          $('#Party_address_side').val(response.side)
          $('#Party_address_postalCode').val(response.postalCode)
          $('#Party_address_city').val(response.city)
          $('#Party_isUnderAddressProtection').prop('checked', response.isUnderAddressProtection)

          // Indicate that identifier was found
          $($identificationLookupButton).removeClass(function () {
            const regExp = /btn-[^\s]*/
            const regExpResult = regExp.exec($(this).attr('class'))

            return regExpResult[0]
          }).addClass('btn-success')
        }
      },
      error: function () {
      }
    })
  })
})
