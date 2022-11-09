/* global $ */

const $identificationTypes = $("[id*='identification_type']")

$identificationTypes.each(function () {
  const $pNumberElement = $('#' + this.id.replace('type', 'pNumber'))

  // Setup listener on change
  $(this)
    .off('change')
    .on('change', function () {
      $pNumberElement.toggle($(this).val() === 'CVR')
    })
    .trigger('change')
})
