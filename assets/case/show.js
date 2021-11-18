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

  const bbr = window.BBR ?? {}
  console.debug({bbr})
  const bbrLeaseDataUrl = bbr.lease?.data_url ?? null
  if (null === bbrLeaseDataUrl) {
      $('#bbr-data-lease').html(bbr.messages?.['Error loading BBR data'] ?? 'Error loading BBR data')
  } else {
    // Load BBR data
    $.ajax(bbrLeaseDataUrl)
      .done(function(data) {
        if (data.rendered) {
          $('#bbr-data-lease').html(data.rendered)
        }
      })
      .fail(function(jqXHR, textStatus, errorThrown ) {
        $('#bbr-data-lease').html(bbr.messages?.['Error loading BBR data'] ?? 'Error loading BBR data')
      });
  }
})
