/* global $ */

// @see https://autocomplete.aws.dk/guide2.html
const dawa = require('dawa-autocomplete2')

const initializeDawaLookup = () => {
  $('[data-dawa-address-lookup]').each(function () {
    if ($(this).find('.data-address-lookup').length > 0) {
      // If data-address-lookup already exists simply return doing nothing
      return
    }

    const config = $(this).data('dawa-address-lookup')
    const selectorPattern = config['selector-pattern'] ?? null
    if (selectorPattern) {
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

      dawa.dawaAutocomplete(input[0], {
        select: selected => {
          const address = selected.data

          // Map address fields.
          const map = {
            // Address field -> DAWA field
            // null: 'id',
            // null: 'status',
            // null: 'darstatus',
            // null: 'vejkode',
            street: 'vejnavn',
            // null: 'adresseringsvejnavn',
            number: 'husnr',
            floor: 'etage',
            side: 'dør',
            // null: 'supplerendebynavn',
            postalCode: 'postnr',
            city: 'postnrnavn'
            // null: 'stormodtagerpostnr',
            // null: 'stormodtagerpostnrnavn',
            // null: 'kommunekode',
            // null: 'adgangsadresseid',
            // null: 'x',
            // null: 'y',
            // null: 'href',
          }
          for (const [addressField, dawaField] of Object.entries(map)) {
            $(selectorPattern.replace('%name%', addressField)).val(address[dawaField] ?? '')
          }
        }
      })
    }
  })
}

// Set up address lookup on document load and ajax load.
window.addEventListener('ajaxload', initializeDawaLookup)
