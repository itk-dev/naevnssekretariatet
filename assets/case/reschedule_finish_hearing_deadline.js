/* global $ */

$('#finish_hearing_submit_button').on('click', function (e) {
  let $form = $('#reschedule_hearing_form')
  e.preventDefault();
  // Submit data via AJAX to the form's action path.
  $.ajax({
    url: $form.attr('action'),
    type: $form.attr('method'),
    data: $form.serialize(),
    success: function (html) {
      if ($(html).find('.form-error-message').length) {
        $('#reschedule_finish_hearing_deadline').modal('hide')
        $(html).modal('show')
      } else {
        window.location.reload();
      }
    },
  });
  return false;
});
