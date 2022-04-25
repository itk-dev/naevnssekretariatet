/* global $ */

$(function () {
  const $cprLookupButton = $('.lookup-cpr')

  $cprLookupButton.on('click', function () {
    const $cprInputField = $('#BoardMember_cpr')
    const $cpr = $cprInputField.val()

    $.ajax({
      url: '/case/new/apply-identifier-data',
      type: 'POST',
      data: {
        type: 'CPR',
        identifier: $cpr
      },
      success: function (response) {
        if ($.isEmptyObject(response)) {
          // No data returned, remove values for now
          $('#BoardMember_name').val('')

          // Indicate that identifier was not found
          $($cprLookupButton).removeClass().addClass('btn-danger btn mt-2 lookup-cpr')
        } else {
          // Insert values into correct html elements
          $('#BoardMember_name').val(response.name)

          // Indicate that identifier was found
          $($cprLookupButton).removeClass().addClass('btn-success btn mt-2 lookup-cpr')
        }
      },
      error: function () {
      }
    })
  })
})
