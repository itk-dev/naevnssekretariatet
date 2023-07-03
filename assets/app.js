import './app.scss'
import * as bootstrap from 'bootstrap'
import '@popperjs/core'
import 'bs-custom-file-input'
import 'select2'

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')

window.addEventListener('load', () => {
  [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
  window.dispatchEvent(new Event('ajaxload'))
})
