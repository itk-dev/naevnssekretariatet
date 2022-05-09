/* global $ */

$(function () {
  // When an element with an all-elements-selector attribute is clicked …
  $('[data-all-elements-selector]').on('click', function () {
    // … we check all elements targeted by the selector.
    $($(this).data('all-elements-selector')).prop('checked', true)
  })
})
