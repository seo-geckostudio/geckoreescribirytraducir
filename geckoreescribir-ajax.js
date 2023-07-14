jQuery(document).ready(function ($) {
    $('.update-openai-meta-box').on('click', function () {
        var post_type = $(this).data('post-type');
        var button = $(this);

        button.text('Actualizando...');
        button.prop('disabled', true);

        $.ajax({
            url: geckoreescribir_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'update_openai_meta_box',
                post_type: post_type,
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    button.text('Actualizar');
                    button.prop('disabled', false);
                    alert('Error al actualizar. Por favor, inténtalo de nuevo.');
                }
            },
            error: function () {
                button.text('Actualizar');
                button.prop('disabled', false);
                alert('Error al actualizar. Por favor, inténtalo de nuevo.');
            },
        });
    });
});
