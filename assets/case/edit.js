/* global $ */
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

const $identificationTypes = $("[id*='Identification_type']")

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
