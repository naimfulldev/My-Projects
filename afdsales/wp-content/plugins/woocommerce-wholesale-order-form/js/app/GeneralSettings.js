jQuery(document).ready(function ($) {
    $('#wwof_general_show_product_thumbnail').on('click', function () {
        var $image_dimensions = $('table.form-table').find('.image_width_settings').parent('tr');
        if ($(this).is(':checked'))
            $image_dimensions.show();
        else
            $image_dimensions.hide();
    });
});
