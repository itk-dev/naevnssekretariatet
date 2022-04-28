/* global $ */

$(function () {
  const $identificationLookupButton = $('#party_form_lookupIdentifier')

  $identificationLookupButton.on('click', function () {
    const $identificationType = $('#party_form_identification_type')
    const $identificationIdentifier = $('#party_form_identification_identifier')

    $.ajax({
      url: '/case/new/apply-identifier-data',
      type: 'POST',
      data: {
        type: $identificationType.val(),
        identifier: $identificationIdentifier.val()
      },
      success: function (response) {
        if ($.isEmptyObject(response)) {
          // No data returned, remove values for now
          $('#party_form_name').val('')
          $('#party_form_address_street').val('')
          $('#party_form_address_number').val('')
          $('#party_form_address_floor').val('')
          $('#party_form_address_side').val('')
          $('#party_form_address_postalCode').val('')
          $('#party_form_address_city').val('')
          $('#party_form_isUnderAddressProtection').prop('checked', false)

          // Indicate that identifier was not found
          $($identificationLookupButton).removeClass().addClass('btn-danger btn identification-lookup')
        } else {
          // Insert values into correct html elements
          $('#party_form_name').val(response.name)
          $('#party_form_address_street').val(response.street)
          $('#party_form_address_number').val(response.number)
          $('#party_form_address_floor').val(response.floor)
          $('#party_form_address_side').val(response.side)
          $('#party_form_address_postalCode').val(response.postalCode)
          $('#party_form_address_city').val(response.city)
          $('#party_form_isUnderAddressProtection').prop('checked', response.isUnderAddressProtection)

          // Indicate that identifier was found
          $($identificationLookupButton).removeClass().addClass('btn-success btn identification-lookup')
        }
      },
      error: function () {
      }
    })
  })
})
