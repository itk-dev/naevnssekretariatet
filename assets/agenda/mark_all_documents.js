/* global $ */

$(function () {
  const $markAllButton = $('#markAllDocumentsButton')

  $markAllButton.on('click', function () {
    $('input:checkbox').prop('checked', true)
  })
})
