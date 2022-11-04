/* global $, location */

require('bootstrap-history-tabs/bootstrap-history-tabs.js')

$(() => {
  // https://github.com/jeffdavidgreen/bootstrap-html5-history-tabs#how-to-use
  $('a[data-toggle="tab"]').historyTabs()

  // Add referer (current url) to links requesting it (via the
  // data-referer-query-name attribute).
  $('a[data-referer-query-name][href]').on('click', function () {
    const url = new URL(this.href)
    const queryName = this.dataset.refererQueryName
    if (!url.searchParams.has(queryName)) {
      url.searchParams.set(queryName, location.href)
    }
    this.href = url.toString()
  })

  // Add referer (current url) to forms requesting it (via the
  // data-referer-query-name attribute).
  $('form[data-referer-query-name][action]').on('submit', function () {
    const url = new URL(this.action)
    const queryName = this.dataset.refererQueryName
    if (!url.searchParams.has(queryName)) {
      url.searchParams.set(queryName, location.href)
    }
    this.action = url.toString()
  })

  $('#create_party_button_id').on('click', function () {
    const fragment = window.location.hash

    if ($.inArray(fragment, ['#landlord', '#neighbour']) !== -1) {
      $('#create_party_button_id').attr('href', $('#create_party_button_id').attr('data-href-counterparty'))
    } else {
      $('#create_party_button_id').attr('href', $('#create_party_button_id').attr('data-href-default'))
    }
  })
})
