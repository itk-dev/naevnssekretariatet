/* global $ */
const $copyAddressButton = $("[id*='copyAddress']")

$copyAddressButton.on('click', function () {
  const $leaseElements = $("input[id*='leaseAddress']")
  // We should copy complainant address into lease address
  for (const item of $leaseElements) {
    // Find value from complainantAddress
    const currentComplainantItem = $('#' + item.id.replace('leaseAddress', 'complainantAddress'))
    item.value = currentComplainantItem.val()
  }
})
