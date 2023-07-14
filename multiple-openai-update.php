<?php

// Crear el menú de la página de actualización masiva
function openai_multiple_update_menu() {
    add_submenu_page('edit.php', 'Generar texto múltiple', 'Generar texto múltiple', 'manage_options', 'openai-multiple-update', 'openai_multiple_update_page');
}
add_action('admin_menu', 'openai_multiple_update_menu');

// Plantilla de la página de actualización masiva
function openai_multiple_update_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes suficientes permisos para acceder a esta página.'));
    }
?>
    <div class="wrap">
        <h1>Generar texto múltiple</h1>
        <p>Aquí irá la tabla con la información y el botón de actualización masiva.</p>
    </div>
<?php
	// Obtener todos los tipos de publicación
$post_types = get_post_types(array('public' => true), 'objects');

// Iniciar tabla
echo '<table class="widefat fixed striped">';
echo '<thead><tr><th>Tipo de publicación</th><th>Total de publicaciones</th><th>Con OpenAI Meta Box</th><th>Sin OpenAI Meta Box</th><th>Acción</th></tr></thead><tbody>';

// Iterar sobre los tipos de publicación y obtener información
foreach ($post_types as $post_type) {
    // Contar publicaciones por tipo de publicación
    $total_posts = wp_count_posts($post_type->name);
    $total_posts_count = $total_posts->publish;

    // Contar publicaciones con OpenAI Meta Box
    $with_openai_meta_box_count = count(
        get_posts(array(
            'post_type' => $post_type->name,
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'openai_meta_value',
                    'value' => '',
                    'compare' => '!=',
                ),
            ),
        ))
    );

    // Calcular publicaciones sin OpenAI Meta Box
    $without_openai_meta_box_count = $total_posts_count - $with_openai_meta_box_count;

    // Mostrar información en la tabla
    echo '<tr>';
    echo '<td>' . $post_type->label . '</td>';
    echo '<td>' . $total_posts_count . '</td>';
    echo '<td>' . $with_openai_meta_box_count . '</td>';
    echo '<td>' . $without_openai_meta_box_count . '</td>';
    echo '<td><button class="button update-openai-meta-box" data-post-type="' . $post_type->name . '">Actualizar2</button></td>';
	
    echo '</tr>';
}

// Finalizar tabla
echo '</tbody></table>';

}
function get_posts_by_post_type_callback() {
    check_ajax_referer('get_posts_by_post_type', 'nonce');

    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
    $posts = get_posts(array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
    ));

    wp_send_json_success($posts);
}

add_action('wp_ajax_get_posts_by_post_type', 'get_posts_by_post_type_callback');

    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes suficientes permisos para acceder a esta página.'));
    }

    // Obtener todos los tipos de publicación
    $post_types = get_post_types(array('public' => true), 'objects');

    // Iniciar tabla
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Tipo de publicación</th><th>Total de publicaciones</th><th>Con OpenAI Meta Box</th><th>Sin OpenAI Meta Box</th><th>Acción</th></tr></thead><tbody>';

    // Iterar sobre los tipos de publicación y obtener información
    foreach ($post_types as $post_type) {
        // Contar publicaciones por tipo de publicación
        $total_posts = wp_count_posts($post_type->name);
        $total_posts_count = $total_posts->publish;

        // Contar publicaciones con OpenAI Meta Box
        $with_openai_meta_box_count = count(
            get_posts(array(
                'post_type' => $post_type->name,
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'openai_meta_value',
                        'value' => '',
                        'compare' => '!=',
                    ),
                ),
            ))
        );

        // Calcular publicaciones sin OpenAI Meta Box
        $without_openai_meta_box_count = $total_posts_count - $with_openai_meta_box_count;

        // Mostrar información en la tabla
        echo '<tr>';
        echo '<td>' . $post_type->label . '</td>';
        echo '<td>' . $total_posts_count . '</td>';
        echo '<td>' . $with_openai_meta_box_count . '</td>';
        echo '<td>' . $without_openai_meta_box_count . '</td>';
       // echo '<td><button class="button update-openai-meta-box" data-post-type="' . $post_type->name . '">Actualizar1</button>';
echo '<td><a href="' . admin_url('options-general.php?page=openai-post-list&post_type=' . $post_type->name) . '" class="button">Pasar a actualización</a></td>';


        echo '</tr>';
    }

    // Finalizar tabla
    echo '</tbody></table>';

function update_openai_meta_box_without_value_callback() {
    check_ajax_referer('geckoreescribir', 'nonce');

    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
    $batch_size = 10; // Puedes ajustar este número según tus necesidades

    if (empty($post_type)) {
        wp_send_json_error('Tipo de publicación no válido.');
    }

    $processed = 0;
    $posts = get_posts(array(
        'post_type' => $post_type,
        'posts_per_page' => $batch_size,
        'meta_query' => array(
            array(
                'key' => 'openai_meta_value',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ));

    foreach ($posts as $post) {
        // Aquí va el código que procesa cada publicación individual
        // ... (puedes copiar la parte relevante de update_openai_meta_box_without_value_callback)
        
        // A continuación se muestra un ejemplo de cómo hacerlo:
        $post_id = $post->ID;

        $language_info = wpml_get_language_information(null, $post_id);
        $language_code = $language_info['locale'];
        $prompt = "Reescribe este contenido en el código de lenguaje ".$language_code." \n " . get_post_field('post_content', $post_id);

        openai_log("Solicitando API - Post ID: {$post_id}, Prompt: {$prompt}");

        $response = wp_remote_post(OPENAI_API_URL, array(
            'method' => 'POST',
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
            'body' => array('prompt' => $prompt, 'clave' => OPENAI_SECRET_KEY),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error('Error al obtener la respuesta de la API de OpenAI: ' . $response->get_error_message(). $prompt);
        }

        $body = wp_remote_retrieve_body($response);
        openai_log("Respuesta API - Body: {$body}");
        $data = json_decode($body, true);

        if (!isset($data['response'])) {
            wp_send_json_error('Error al obtener la respuesta de la API de OpenAI: ' . $body);
        }

        $openai_value = sanitize_textarea_field($data['response']);
        update_post_meta($post_id, 'openai_meta_value', $openai_value);

        $processed++;
    }

    wp_send_json_success(array('processed' => $processed));
}



add_action('wp_ajax_update_openai_meta_box_without_value', 'update_openai_meta_box_without_value_callback');
?>
<script>
    jQuery(document).ready(function($) {
        const updateProgress = (percentage) => {
            $('#openai-progress .openai-progress-bar').css('width', percentage + '%');
        };

        const updatePostsWithoutValue = (postType, total, processed) => {
            if (processed >= total) {
                updateProgress(100);
                alert('Todos los metadatos se han actualizado correctamente.');
                return;
            }

            const data = {
                'action': 'update_openai_meta_box_without_value',
                'post_type': postType,
                'nonce': geckoreescribir_ajax_obj.nonce,
            };

            $.post(geckoreescribir_ajax_obj.ajax_url, data, (response) => {
                if (response.success) {
                    processed += response.data.processed;
                    updateProgress(Math.round((processed / total) * 100));
                    updatePostsWithoutValue(postType, total, processed);
                } else {
                    alert('Error: ' + response.data);
                }
            });
        };


    });
</script>

<div id="openai-progress" style="display: none; margin: 10px 0;">
    <div class="openai-progress-bar" style="background-color: #0073aa; height: 20px; width: 0;"></div>
</div>

