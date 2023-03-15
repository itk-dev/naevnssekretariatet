/* global $ */

$(function () {
  const $cprLookupButton = $('.lookup-cpr')

  $cprLookupButton.on('click', function () {
    const $cprInputField = $('#BoardMember_cpr')
    const $cpr = $cprInputField.val()

    $.ajax({
      url: '/case/new/apply-identifier-data',
      data: {
        type: 'CPR',
        identifier: $cpr
      },
      success: function (response) {
        if ($.isEmptyObject(response)) {
          // No data returned, remove values for now
          $('#BoardMember_name').val('')

          // Indicate that identifier was not found
          $($cprLookupButton).removeClass(function () {
            const regExp = /btn-[^\s]*/
            const regExpResult = regExp.exec($(this).attr('class'))

            return regExpResult[0]
          }).addClass('btn-danger')
        } else {
          // Insert values into correct html elements
          $('#BoardMember_name').val(response.name)

          // Indicate that identifier was found
          $($cprLookupButton).removeClass(
            function () {
              const regExp = /btn-[^\s]*/
              const regExpResult = regExp.exec($(this).attr('class'))

              return regExpResult[0]
            }
          ).addClass('btn-success')
        }
      },
      error: function () {
      }
    })
  })
})
