/* global $ */

// @see https://github.com/Klimadatastyrelsen/adressevaelger
import { adressevaelger } from './vendor/adressevaelger/adressevaelger.esm.js'

import './vendor/adressevaelger/adressevaelger.css'
import './address-lookup.scss'

// adressevaelger.dk/adresser/<id> returns a deeply nested
// structure rooted on `adresse`.
const extractAddressFields = response => {
  const adresse = response?.adresse
  const husnummer = adresse?.husnummer
  return {
    street: husnummer?.vejnavn ?? '',
    number: husnummer?.husnummertekst ?? '',
    floor: adresse?.etagebetegnelse ?? '',
    side: adresse?.doerbetegnelse ?? '',
    postalCode: husnummer?.postnummer?.postnr ?? '',
    city: husnummer?.postnummer?.navn ?? ''
  }
}

const initializeAddressLookup = () => {
  $('[data-address-lookup]').each(function () {
    if ($(this).find('.address-lookup-container').length > 0) {
      // If data-address-lookup already exists, simply return doing nothing.
      return
    }

    const config = $(this).data('address-lookup')
    const selectorPattern = config['selector-pattern'] ?? null
    if (!selectorPattern) {
      return
    }

    const token = config.token
    if (!token) {
      return
    }

    const input = $('<input class="form-control"/>')
    if (config.placeholder) {
      input.attr('placeholder', config.placeholder)
    }

    const form = $('<div class="form-group data-address-lookup"/>')
    form.append(input)

    if (config.help) {
      form.append($('<small class="form-text text-muted"/>').html(config.help))
    }

    $(this).prepend(form)

    const inputContainer = $('<div class="autocomplete-container"/>').append(input)
    const wrapper = $('<div class="form-group address-lookup-container"/>')
      .append(inputContainer)

    $(this).prepend(wrapper)

    const options = {
      token,
      select: selected => {
        const fields = extractAddressFields(selected)
        for (const [addressField, value] of Object.entries(fields)) {
          $(selectorPattern.replace('%name%', addressField)).val(value ?? '')
        }
      }
    }

    if (config['api-url']) {
      options.apiUrl = config['api-url']
    }

    adressevaelger(input[0], options)
  })
}

window.addEventListener('ajaxload', initializeAddressLookup)
