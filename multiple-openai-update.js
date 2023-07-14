jQuery(document).ready(function ($) {
    $('.update-openai-meta-box').on('click', function () {
        var postType = $(this).data('post-type');
        console.log('Actualizar OpenAI Meta Box para el tipo de publicación:', postType);
        
        // Aquí va la lógica para mostrar la lista de publicaciones y permitir la selección

        // Aquí va la lógica para actualizar las publicaciones seleccionadas usando la función openai_update_meta_ajax o una función similar
    });
});
jQuery(document).ready(function ($) {
    $('.show-posts-button').on('click', function (e) {
        e.preventDefault();

        const postType = $(this).data('post-type');
        const nonce = $(this).data('nonce');

        $.ajax({
            url: openai_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_posts_by_post_type',
                post_type: postType,
                nonce: nonce,
            },
            success: function (response) {
                // Aquí puedes mostrar la lista de publicaciones y agregar funcionalidad para seleccionar publicaciones y actualizarlas masivamente.
            },
            error: function (response) {
                // Maneja los errores aquí.
            },
        });
    });
});
jQuery(document).on('click', '.update-openai-meta-box-without-value', function(e) {
	
    e.preventDefault();
    let $this = jQuery(this);
    let post_type = $this.data('post-type');
    let data = {
        'action': 'update_openai_meta_box_without_value',
        'post_type': post_type,
        'nonce': geckoreescribir_ajax_obj.nonce,
    };
alert(data);
    jQuery.post(geckoreescribir_ajax_obj.ajax_url, data, function(response) {
        if (response.success) {
            alert('Metadatos actualizados correctamente para publicaciones sin valor de OpenAI Meta Box.');
        } else {
            alert('Error: ' + response.data);
        }
    });
});
async function updateSingleOpenAiMetaBox(postId, nonce) {
    return new Promise(async (resolve, reject) => {
        var data = {
            'action': 'update_single_openai_meta_box',
            'post_id': postId,
            'nonce': nonce,
        };

        await $.post(openai_ajax.ajax_url, data, function (response) {
            if (response.success) {
                $('.openai-meta-box-' + postId).text(response.data);
                resolve();
            } else {
                reject(response.data);
            }
        });
    });
}

jQuery(document).ready(function ($) {
    // Seleccionar todos los checkboxes
    $('#select-all-checkboxes').on('click', function () {
        $('.openai-post-checkbox').prop('checked', true);
    });

    // Actualizar todos los campos chequeados
$('#update-selected-posts').on('click', async function () {
    var nonce = openai_ajax.nonce;

    var checkedPosts = $('.openai-post-checkbox:checked');

    for (var i = 0; i < checkedPosts.length; i++) {
        var postId = $(checkedPosts[i]).val();

        try {
            await updateSingleOpenAiMetaBox(postId, nonce);
            console.log("Actualizado correctamente el post ID:", postId);
        } catch (error) {
            console.error("Error al actualizar el post ID:", postId, "Error:", error);
            alert('Error al actualizar el post ID ' + postId + ': ' + error);
        }
    }
});

});
jQuery(document).ready(function ($) {
    // Actualizar OpenAI Meta Box de un solo post
    jQuery(document).off('click', '.update-single-openai-meta-box').on('click', '.update-single-openai-meta-box', function(e) {
        var postId = $(this).data('post-id');
        var nonce = openai_ajax.nonce;

        var data = {
            'action': 'update_single_openai_meta_box',
            'post_id': postId,
            'nonce': nonce,
        };

        $.post(openai_ajax.ajax_url, data, function (response) {
            if (response.success) {
                 $('.openai-meta-box-' + postId).text(response.data);
            } else {
                alert('Error: ' + response.data);
            }
        });
    });
});
