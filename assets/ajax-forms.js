/* global $ */

/*
 * Handle AJAX post of forms when clicking a `[type="submit"]` button.
 *
 * Enable on a form by setting the data-use-ajax attribute, e.g.
 *
 *   <form data-use-ajax …>…</form>
 *
 * If the response from the AJAX call generates an error, the error message will
 * be shown in any `.form-error` element inside the form.
 *
 * On success, if any `.form-error-message` elements exist in the HTML response,
 * the `.form-body` element will be replaced with the `.form-body` element from
 * the response to show the error messages.
 *
 * Otherwise, a full page reload will be performed.
 *
 * Full(er) example with `.form-body` and `.form-error` elements:
 *
 * <form action="…" data-use-ajax …>
 *  <div class="form-body">
 *   [form fields]
 *   <div class="form-error">
 *  </div>
 *  <div class="form-footer">
 *   <button type="submit">Submit</button>
 *  </div>
 * </form>
 *
 * Note that this only works if the submit button is *outside* the `.form-body`
 * element.
 */

$(() => {
  const handleAjaxPost = (e) => {
    const $form = $(e.target).closest('form')

    e.preventDefault()

    // Submit data via AJAX to the form's action path.
    let url = $form.attr('action')
    // Tell that we're ajax'ing.
    url += (url.indexOf('?') < 0 ? '?' : '&') + 'ajax=1'
    $.ajax({
      url,
      type: $form.attr('method'),
      data: $form.serialize(),
      success: function (html) {
        if ($(html).find('.invalid-feedback').length > 0) {
          // Replace form body with form body from response
          $form.find('.form-body').replaceWith($(html).find('.form-body'))
        } else {
          window.location.reload()
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        // Display any errors.
        $form.find('.form-error').html(errorThrown || textStatus)
      }
    })

    return false
  }

  $('form[data-use-ajax]').each(function () {
    $(this).find('[type="submit"]').on('click', handleAjaxPost)
  })
})
