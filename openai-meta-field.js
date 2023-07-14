jQuery(document).ready(function ($) {
    $('#update_openai_meta').on('click', function () {
        $.ajax({
            type: 'POST',
            url: openai_ajax_object.ajax_url,
            data: {
                action: 'openai_update_meta',
                post_id: openai_ajax_object.post_id,
            },
            beforeSend: function () {
                $('#update_openai_meta').text('Actualizando...').prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    // Actualizar el contenido del div editable
                    $('#openai_meta_value').html(response.data);

                    $('#update_openai_meta').text('Actualizar').prop('disabled', false);
                } else {
                    alert(response.data);
                    $('#update_openai_meta').text('Actualizar').prop('disabled', false);
                }
            },
            error: function () {
                alert('Error al realizar la petición de actualización. Por favor, inténtalo de nuevo.');
                $('#update_openai_meta').text('Actualizar').prop('disabled', false);
            },
        });
    });
});