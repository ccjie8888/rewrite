jQuery(document).ready(function ($) {
    console.log("Document ready.");

    $('.variations_form').on('change', 'select', function () {
        var selectedOption = $(this).val();
        console.log("Selected option: " + selectedOption);

        // 获取当前URL路径并去掉颜色部分
        var currentPath = window.location.pathname;
        var basePath = currentPath.replace(/\/[^\/]+\/?$/, '');  // 移除最后的颜色部分

        console.log("Base Path: " + basePath);

        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_product_variation',
                custom_product_path: basePath.replace(/^\/shop\//, ''), // Remove the initial '/shop/' part for correct matching
                attribute_pa_color: selectedOption,
            },
            success: function (response) {
                if (response.success) {
                    $('.product-content').html(response.data.content);
                    var newUrl = basePath + '/' + selectedOption + '/';
                    console.log("New URL: " + newUrl);
                    history.pushState(null, '', newUrl);
                } else {
                    console.log("Failed to load product variation.");
                }
            },
            error: function () {
                console.log("Error with the AJAX request.");
            }
        });
    });
});
