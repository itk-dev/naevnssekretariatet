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
