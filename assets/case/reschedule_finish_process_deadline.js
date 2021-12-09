/* global $ */

$('#finish_process_submit_button').on('click', function (e) {
  const $form = $('#reschedule_process_form')
  e.preventDefault()
  // Submit data via AJAX to the form's action path.
  $.ajax({
    url: $form.attr('action'),
    type: $form.attr('method'),
    data: $form.serialize(),
    success: function (html) {
      if ($(html).find('.form-error-message').length) {
        $('#reschedule_finish_process_deadline').modal('hide')
        $(html).modal('show')
      } else {
        window.location.reload()
      }
    }
  })
  return false
})
