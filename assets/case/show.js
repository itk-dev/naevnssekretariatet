/* global $, location */

require('bootstrap-history-tabs/bootstrap-history-tabs.js')

$(() => {
  // https://github.com/jeffdavidgreen/bootstrap-html5-history-tabs#how-to-use
  $('a[data-toggle="tab"]').historyTabs()

  // Add current url to links requesting it (via the data-referer-query-name
  // attribute).
  $('a[data-referer-query-name][href]').each(function () {
    const url = new URL(this.href)
    const queryName = this.dataset.refererQueryName
    if (!url.searchParams.has(queryName)) {
      url.searchParams.set(queryName, location.href)
    }
    this.href = url.toString()
  })
})
