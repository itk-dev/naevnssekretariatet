var $board = $('#case_entity_board');
$board.change(function () {
    var $form = $(this).closest('form');
    var data = {};
    data[$board.attr('name')] = $board.val();
    // Submit data via AJAX to the form's action path.
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
            $('#case_entity_caseEntity').replaceWith(
                $(html).find('#case_entity_caseEntity')
            );
        }
    });
});