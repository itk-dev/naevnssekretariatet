import './app.scss'
import * as bootstrap from 'bootstrap'
import '@popperjs/core'
import 'bs-custom-file-input'
import $ from 'jquery'
import 'select2'

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')

$(document).ready(() => {
  [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
  window.dispatchEvent(new Event('ajaxload'))
})
