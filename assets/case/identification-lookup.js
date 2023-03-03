/* global $ */

window.addEventListener('ajaxload', function () {
  const lookupElements = $('.identification-lookup')

  for (const lookupElement of lookupElements) {
    const $lookupButton = $('#' + lookupElement.id)
    const htmlIdPrefix = '#' + lookupElement.id.substring(0, lookupElement.id.lastIndexOf('_')) + '_' + $lookupButton.data('specifier')
    $lookupButton.on('click', function () {
      const $identifierType = $(htmlIdPrefix + 'Identification_type').val()
      const $identifier = $(htmlIdPrefix + 'Identification_identifier').val()

      $.ajax({
        url: '/case/new/apply-identifier-data',
        data: {
          type: $identifierType,
          identifier: $identifier
        },
        success: function (response) {
          if ($.isEmptyObject(response)) {
            // No data returned, remove values for now
            $(htmlIdPrefix).val('')
            $(htmlIdPrefix + 'Address_street').val('')
            $(htmlIdPrefix + 'Address_number').val('')
            $(htmlIdPrefix + 'Address_floor').val('')
            $(htmlIdPrefix + 'Address_side').val('')
            $(htmlIdPrefix + 'Address_postalCode').val('')
            $(htmlIdPrefix + 'Address_city').val('')
            $(htmlIdPrefix + 'IsUnderAddressProtection').prop('checked', false)

            // Indicate that identifier was not found
            $($lookupButton).removeClass(function () {
              const regExp = /btn-[^\s]*/
              const regExpResult = regExp.exec($(this).attr('class'))

              return regExpResult[0]
            }).addClass('btn-danger btn')
          } else {
            // Insert values into correct html elements
            $(htmlIdPrefix).val(response.name)
            $(htmlIdPrefix + 'Address_street').val(response.street)
            $(htmlIdPrefix + 'Address_number').val(response.number)
            $(htmlIdPrefix + 'Address_floor').val(response.floor)
            $(htmlIdPrefix + 'Address_side').val(response.side)
            $(htmlIdPrefix + 'Address_postalCode').val(response.postalCode)
            $(htmlIdPrefix + 'Address_city').val(response.city)
            $(htmlIdPrefix + 'IsUnderAddressProtection').prop('checked', response.isUnderAddressProtection)

            // Indicate that identifier was found
            $($lookupButton).removeClass(function () {
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
  }
})
