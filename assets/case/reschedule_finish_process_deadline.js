/* global $ */

$(() => {
  const $form = $('#reschedule_process_form')
  const $modal = $('#reschedule_finish_process_deadline')
  $form.find('[type="submit"]').on('click', function (e) {
    e.preventDefault()
    let url = $form.attr('action')
    // Tell that we're ajax'ing.
    url += (url.indexOf('?') < 0 ? '?' : '&') + 'ajax=1'
    // Submit data via AJAX to the form's action path.
    $.ajax({
      url: url,
      type: $form.attr('method'),
      data: $form.serialize(),
      success: function (html) {
        if ($(html).find('.form-error-message').length) {
          // Replace modal body with modal body from response
          $modal.find('.modal-body').replaceWith($(html).find('.modal-body'))
        } else {
          window.location.reload()
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        // @todo Handle errors.
        console.debug(textStatus, errorThrown)
      }
    })

    return false
  })
})
