var $muni = $('#case_type_selector_municipality');
// When sport gets selected ...
$muni.change(function() {
    // ... retrieve the corresponding form.
    var $form = $(this).closest('form');
    // Simulate form data, but only include the selected sport value.
    var data = {};
    data[$muni.attr('name')] = $muni.val();
    // Submit data via AJAX to the form's action path.
    $.ajax({
        url : $form.attr('action'),
        type: $form.attr('method'),
        data : data,
        success: function(html) {
            // Replace current position field ...
            $('#case_type_selector_board').replaceWith(
                // ... with the returned one from the AJAX response.
                $(html).find('#case_type_selector_board')
            );
            // Position field now displays the appropriate positions.
        }
    });
});