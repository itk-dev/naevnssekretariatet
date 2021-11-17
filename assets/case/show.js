/* global $ */

require('bootstrap-history-tabs/bootstrap-history-tabs.js')

$(() => {
    // https://github.com/jeffdavidgreen/bootstrap-html5-history-tabs#how-to-use
    $('a[data-toggle="tab"]').historyTabs()
})
