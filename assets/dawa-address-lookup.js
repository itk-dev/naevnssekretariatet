/* global $ */

// @see https://autocomplete.aws.dk/guide2.html
const dawa = require('dawa-autocomplete2')

$(() => {
  $('[data-dawa-address-lookup]').each(function () {
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
          console.log(address)

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
})
