/* global $ */

$(() => {
  $('.tvist1-search').on('submit', function (event) {
    // alert("Submitted");
    event.preventDefault();
    event.stopPropagation();
    let input = $(this).find('input').val();

    window.location.href = '/search/?search=' + input;

    return false;
  });
})
