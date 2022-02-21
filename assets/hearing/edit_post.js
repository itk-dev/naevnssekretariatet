import './edit_post.scss'

// https://symfony.com/doc/current/reference/forms/types/collection.html#adding-and-removing-items
const $ = require('jquery')

$(document).ready(() => {
  const initialze = () => {
    $('[data-remove-item]').off('click').on('click', function (e) {
      $(this).closest('li').remove()
    })
  }

  $('.add-another-collection-widget').on('click', function (e) {
    const list = $($(this).attr('data-list-selector'))
    // Try to find the counter of the list or use the length of the list
    let counter = list.data('widget-counter') || list.children().length

    // grab the prototype template
    const newWidget = list.attr('data-prototype').replace(/__name__/g, counter)
    // Increase the counter
    counter++
    // And store it, the length cannot be used if deleting widgets is allowed
    list.data('widget-counter', counter)

    // create a new list element and add it to the list
    const newElem = $(list.attr('data-widget-tags')).html(newWidget)
    newElem.appendTo(list)

    initialze()
  })

  initialze()
})
