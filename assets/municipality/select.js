/* global $ */

$(document).ready(function () {
  $('form[name="municipality_selector"]').on('change', function () {
    $(this).closest('form').submit()
  })
})
