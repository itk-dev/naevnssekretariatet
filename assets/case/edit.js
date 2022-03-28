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

const $identifications = $("[id*='Identification_type']")

$identifications.each(function () {
  const $pNumberElement = $('#' + this.id.replace('type', 'pNumber'))

  if ($(this).val() === 'CPR') {
    $pNumberElement.hide()
  }

  $(this)
    .off('change')
    .on('change', function () {
      if ($(this).val() === 'CPR') {
        $pNumberElement.hide()
      } else {
        $pNumberElement.show()
      }
    })
})
