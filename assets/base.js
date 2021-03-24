/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './base.scss'

const $ = require('jquery')

require('bootstrap')

// Note this requires .autoProvidejQuery() in webpack.config.js
require('datatables.net')

$(document).ready(function () {
  $('[data-toggle="popover"]').popover()
  $('[data-toggle="tooltip"]').tooltip({
    delay: { show: 1000, hide: 100 }
  })
  $('#casetable').DataTable({
    paginate: false,
    info: false,
    filter: false
  })
})
