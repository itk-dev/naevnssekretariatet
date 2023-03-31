/* global Event */
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './app.scss'
import * as bootstrap from 'bootstrap'
require('@popperjs/core')

// @see https://getbootstrap.com/docs/4.6/components/forms/#file-browser
const bsCustomFileInput = require('bs-custom-file-input')

// Enable tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

const $ = require('jquery')
require('select2')

$(document).ready(function () {
  tooltipList.init()
  bsCustomFileInput.init()
})

window.addEventListener('load', () => {
  // Trigger ajax load on document load.
  window.dispatchEvent(new Event('ajaxload'))
})
