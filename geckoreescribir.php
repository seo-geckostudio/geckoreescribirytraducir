<?php

/*

Plugin Name: Geckoreescribir

Description: Añade un campo meta a los posts con el resultado de la API de OpenAI del servidor local.

Version: 1.0

Author: Tu nombre

*/
add_action('wp_ajax_update_single_openai_meta_box', 'update_single_openai_meta_box_callback');

function update_single_openai_meta_box_callback() {
    // Verificar nonce
    check_ajax_referer('openai-update', 'nonce');

    // Obtener el ID del post
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    // Llamar a la función openai_update_meta_ajax() con el ID del post
    openai_update_meta_ajax($post_id);

    // No olvides finalizar la ejecución al final de la función AJAX
    wp_die();
}

function openai_post_list_admin_page() {
    add_submenu_page(
        null, // No mostrará esta página en el menú de administración
        'Actualizar OpenAI Meta Box', // Título de la página
        '', // Título del menú (no aplicable aquí)
        'manage_options', // Capacidad requerida
        'openai-post-list', // Identificador único de la página
        'openai_post_list_page_content' // Función de callback para renderizar el contenido
    );
}
add_action('admin_menu', 'openai_post_list_admin_page');
function openai_post_list_page_content() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes suficientes permisos para acceder a esta página.'));
    }

    $post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : '';

    if (empty($post_type)) {
        wp_die(__('El tipo de publicación no es válido.'));
    }

    $posts = get_posts(array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
    ));

    echo '<h1>Actualizar OpenAI Meta Box para ' . esc_html($post_type) . '</h1>';

    // Botones para seleccionar todos los checkboxes y actualizar todos los campos chequeados
    echo '<button id="select-all-checkboxes" class="button">Seleccionar todos</button> ';
    echo '<button id="update-selected-posts" class="button">Actualizar campos chequeados</button>';

    // Renderizar la tabla con las publicaciones
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Checkbox</th><th>ID</th><th>Título</th><th>Idioma</th><th>OpenAI Meta Box</th><th>Acción</th></tr></thead><tbody>';

    foreach ($posts as $post) {
        $openai_meta_box = get_post_meta($post->ID, 'openai_meta_value', true);
		$language_details = apply_filters('wpml_post_language_details', NULL, $post->ID);
        $language_code = isset($language_details['language_code']) ? $language_details['language_code'] : '';
        echo '<tr>';
        echo '<td><input type="checkbox" class="openai-post-checkbox" value="' . $post->ID . '"></td>';
        echo '<td>' . $post->ID . '</td>';
        echo '<td>' . esc_html($post->post_title) . '</td>';
		echo '<td>' . esc_html($language_code) . '</td>'; // Muestra el código del idioma
        echo '<td class="openai-meta-box-' . $post->ID  . '">' . esc_html($openai_meta_box) . '</td>';
       // echo '<td><button class="button update-openai-meta-box" data-post-id="' . $post->ID . '">Actualizar</button></td>';
		// ...
echo '<td><button class="button update-single-openai-meta-box" data-post-id="' . $post->ID . '">Actualizar</button></td>';
// ...

        echo '</tr>';
    }

    echo '</tbody></table>';
}


/*function openai_create_admin_page() {
    add_menu_page(
        'Actualizar OpenAI Meta Box',
        'Actualizar OpenAI Meta Box',
        'manage_options',
        'openai-update-meta-box',
        'openai_render_admin_page'
    );
}
add_action('admin_menu', 'openai_create_admin_page');*/
function openai_render_admin_page() {
    $post_type = 'tu_post_type'; // Reemplaza esto con el tipo de publicación que deseas

    $posts = get_posts(array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
    ));

    echo '<div class="wrap">';
    echo '<h1>Actualizar OpenAI Meta Box para ' . $post_type . '</h1>';

    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th><input type="checkbox" id="select-all-posts"></th><th>ID</th><th>Título</th><th>Acción</th></tr></thead><tbody>';

    foreach ($posts as $post) {
        echo '<tr>';
        echo '<td><input type="checkbox" class="post-checkbox" value="' . $post->ID . '"></td>';
        echo '<td>' . $post->ID . '</td>';
        echo '<td>' . $post->post_title . '</td>';
        echo '<td><button class="button update-openai-meta-box" data-post-id="' . $post->ID . '">Actualizar</button></td>';
		

        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';

   ?>
<script>
jQuery(document).ready(function() {
    // Manejar el clic en el botón "Actualizar"
    jQuery(document).on('click', '.update-openai-meta-box', function(e) {
        e.preventDefault();
        var postId = jQuery(this).data('post-id');

        // Aquí es donde enviarás la petición AJAX a tu servidor local para obtener la respuesta de OpenAI
        // y actualizar el campo openai_meta_value para la publicación con el ID dado

        // Esta es una plantilla de cómo puede verse la petición AJAX:
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'update_openai_meta_box', // Nombre de la acción que coincida con la función que ya tienes
                post_id: postId,
                nonce: geckoreescribir_ajax_obj.nonce, // Asegúrate de que este objeto exista
            },
            success: function(response) {
                if (response.success) {
                    alert('Metadatos actualizados correctamente.');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error en la petición AJAX.');
            }
        });
    });

    // Código para seleccionar / deseleccionar todos los elementos de la lista
    jQuery('#select-all-posts').on('click', function() {
        var isChecked = jQuery(this).is(':checked');
        jQuery('.post-checkbox').prop('checked', isChecked);
    });
});
</script>
<?php

}
function openai_enqueue_admin_scripts() {
    wp_enqueue_script('openai-admin-js', plugin_dir_url(__FILE__) . 'multiple-openai-update.js', array('jquery'), '1.10.1', true);
    
    wp_localize_script('openai-admin-js', 'openai_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('openai-update')
    ));

    wp_localize_script('openai-admin-js', 'geckoreescribir_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('geckoreescribir_ajax_nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'openai_enqueue_admin_scripts');


function openai_plugin_bulk_generate_menu() {
    add_submenu_page(
        'edit.php',
        'Generar texto múltiple',
        'Generar texto múltiple',
        'manage_options',
        'openai-bulk-generate',
        'openai_bulk_generate_callback'
    );
}
add_action('admin_menu', 'openai_plugin_bulk_generate_menu');


// Asegúrate de cambiar 'tu_url_del_servidor_local' a la URL real de tu servidor local

define('OPENAI_API_URL', 'https://gecko.gscloud.es/quality_functions/usoia.php');

define('OPENAI_SECRET_KEY', 'tu_clave_secreta');
function openai_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'openai-log.txt';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}


function openai_plugin_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes suficientes permisos para acceder a esta página.'));
    }

    // Lógica de guardado de opciones aquí
    if (isset($_POST['submit']) && isset($_POST['post_types']) && isset($_POST['openai_options_nonce']) && wp_verify_nonce($_POST['openai_options_nonce'], 'openai_save_options')) {
        $selected_post_types = array_map('sanitize_text_field', $_POST['post_types']);
        update_option('openai_selected_post_types', $selected_post_types);
    }

    $selected_post_types = get_option('openai_selected_post_types', array());
    $post_types = get_post_types(array('public' => true), 'objects');
    ?>
    <div class="wrap">
        <h1>Opciones de OpenAI Meta Field</h1>
        <form method="post" action="">
            <?php wp_nonce_field('openai_save_options', 'openai_options_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="post_types">Selecciona los tipos de publicación:</label></th>
                    <td>
                        <select name="post_types[]" id="post_types" multiple="multiple" size="5" style="width: 100%;">
                            <?php foreach ($post_types as $post_type): ?>
                                <option value="<?php echo $post_type->name; ?>" <?php echo in_array($post_type->name, $selected_post_types) ? 'selected' : ''; ?>><?php echo $post_type->label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar cambios">
            </p>
        </form>
    </div>
    <?php
}


function openai_register_meta_boxes() {
    $selected_post_types = get_option('openai_selected_post_types', array());
    foreach ($selected_post_types as $post_type) {
        add_meta_box('openai_meta_box', 'Campo Meta de OpenAI', 'openai_meta_box_callback', $post_type);
    }
}
add_action('add_meta_boxes', 'openai_register_meta_boxes');



function openai_meta_box_callback($post) {

    wp_nonce_field(basename(__FILE__), 'openai_nonce');

    $openai_value = get_post_meta($post->ID, 'openai_meta_value', true);

   // echo '<textarea style="width:100%;" rows="5" readonly>' . esc_textarea($openai_value) . '</textarea>';
echo '<div id="openai_meta_value" style="width:100%; min-height:100px; border: 1px solid #ccc; padding: 6px 10px;" contenteditable="true">' . esc_html($openai_value) . '</div>';
    echo '<button type="button" class="button" id="update_openai_meta">Actualizar</button>';
echo '<input type="hidden" id="openai_meta_value_hidden" name="openai_meta_value" value="' . esc_attr($openai_value) . '">';
    echo '<script>
            document.getElementById("post").addEventListener("submit", function() {
                var openaiMetaValue = document.getElementById("openai_meta_value").innerHTML;
                document.getElementById("openai_meta_value_hidden").value = openaiMetaValue;
            });
          </script>';
    wp_enqueue_script('openai_meta_js', plugin_dir_url(__FILE__) . 'openai-meta-field.js', array('jquery'), '1.0.0', true);

    wp_localize_script('openai_meta_js', 'openai_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'post_id' => $post->ID));

}



function openai_save_meta_box_data($post_id) {
    if (!isset($_POST['openai_nonce']) || !wp_verify_nonce($_POST['openai_nonce'], basename(__FILE__))) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Leer el contenido actualizado del div editable a través de $_POST
    $openai_value = isset($_POST['openai_meta_value']) ? $_POST['openai_meta_value'] : '';

    update_post_meta($post_id, 'openai_meta_value', $openai_value);
}


add_action('save_post', 'openai_save_meta_box_data');



function openai_update_meta_ajax($post_id) {

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (!current_user_can('edit_post', $post_id)) {

        wp_send_json_error('No tienes permiso para editar este post.');

    }
$language_info = wpml_get_language_information(null, $post_id);
    $language_code = $language_info['locale'];
  $prompt = "Como experto en Marketing de contenidos, reescribe este texto para que sea más comercial. Devuelve el resultado formateado en HTML y siempre traducido en el código de lenguaje ".$language_code.". Por favor, separa los párrafos con etiquetas p \n " . get_post_field('post_content', $post_id);

    // Registro de solicitud
    openai_log("Solicitando API - Post ID: {$post_id}, Prompt: {$prompt}");


    $response = wp_remote_post(OPENAI_API_URL, array(

        'method' => 'POST',

        'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),

        'body' => array('prompt' => $prompt, 'clave' => OPENAI_SECRET_KEY),
		'timeout' => 120,

    ));

    if (is_wp_error($response)) {

        wp_send_json_error('Error al obtener la respuesta de la API de OpenAI v2: ' . $response->get_error_message(). $prompt);

    }



    $body =    wp_remote_retrieve_body($response);
openai_log("Respuesta API - Body: {$body}");
    $data = json_decode($body, true);



    if (!isset($data['response'])) {

        wp_send_json_error('Error al obtener la respuesta de la API de OpenAI v3: ' . $body);

    }



    $openai_value = sanitize_textarea_field($data['response']);

    update_post_meta($post_id, 'openai_meta_value', $openai_value);



    wp_send_json_success($openai_value);

}

add_action('wp_ajax_openai_update_meta', 'openai_update_meta_ajax');


function openai_bulk_generate_callback() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes suficientes permisos para acceder a esta página.'));
    }

    $post_types = get_post_types(array('public' => true), 'objects');
    ?>
    <div class="wrap">
        <h1>Generar texto múltiple</h1>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Tipo de publicación</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Total de publicaciones</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Con OpenAI Meta Field</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Sin OpenAI Meta Field</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Acciones</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="manage-column column-columnname" scope="col">Tipo de publicación</th>
                    <th class="manage-column column-columnname" scope="col">Total de publicaciones</th>
                    <th class="manage-column column-columnname" scope="col">Con OpenAI Meta Field</th>
                    <th class="manage-column column-columnname" scope="col">Sin OpenAI Meta Field</th>
                    <th class="manage-column column-columnname" scope="col">Acciones</th>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($post_types as $post_type): ?>
                    <tr>
                        <td><?php echo $post_type->label; ?></td>
                        <td>
                            <?php
                            $total_posts = wp_count_posts($post_type->name)->publish;
                            echo $total_posts;
                            ?>
                        </td>
                        <td>
                            <?php
                            $openai_meta_filled = new WP_Query(array(
                                'post_type' => $post_type->name,
                                'post_status' => 'publish',
                                'meta_query' => array(
                                    array(
                                        'key' => 'openai_meta_value',
                                        'value' => '',
                                        'compare' => '!=',
                                    ),
                                ),
                            ));
                            echo $openai_meta_filled->found_posts;
                            ?>
                        </td>
                        <td>
                            <?php
                            $openai_meta_empty = new WP_Query(array(
                                'post_type' => $post_type->name,
                                'post_status' => 'publish',
                                'meta_query' => array(
                                    array(
                                        'key' => 'openai_meta_value',
                                        'value' => '',
                                        'compare' => '=',
                                    ),
                                ),
                            ));
                            echo $openai_meta_empty->found_posts;
                            ?>
                        </td>
                        <td>
                            <button type="button" class="button" data-post-type="<?php echo $post_type->name; ?>">Actualizar OpenAI Meta Field</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function openai_bulk_generate_menu() {
    add_submenu_page(
        'options-general.php',
        'Generar texto múltiple',
        'Generar texto múltiple',
        'manage_options',
        'openai-multiple-text',
        'openai_multiple_text_options'
    );
}

function openai_multiple_text_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes suficientes permisos para acceder a esta página.'));
    }

    include(plugin_dir_path(__FILE__) . 'multiple-openai-update.php');
}

add_action('admin_menu', 'openai_bulk_generate_menu');

function openai_plugin_menu() {
    add_options_page('Opciones de OpenAI Meta Field', 'OpenAI Meta Field', 'manage_options', 'openai-meta-field-options', 'openai_plugin_options');

    add_submenu_page(
        'openai-meta-field-options',
        'Generar Texto Múltiple',
        'Generar Texto Múltiple',
        'manage_options',
        'openai-generar-texto-multiple',
        'openai_multiple_text_options'
    );
}
add_action('admin_menu', 'openai_plugin_menu');
function geckoreescribir_enqueue_scripts() {
    wp_enqueue_script('geckoreescribir-ajax', plugins_url('geckoreescribir-ajax.js', __FILE__), array('jquery'), false, true);
    wp_localize_script('geckoreescribir-ajax', 'geckoreescribir_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('admin_enqueue_scripts', 'geckoreescribir_enqueue_scripts');
function update_openai_meta_box_callback() {
    // Verificar nonce y permisos
    check_ajax_referer('update_openai_meta_box_nonce', 'nonce');
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('No tienes suficientes permisos para realizar esta acción.');
    }

    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';

    // Obteniendo todas las publicaciones del tipo especificado en $post_type
    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Hacer una llamada a la función openai_update_meta_ajax()
            $_POST['post_id'] = $post_id;
            ob_start();
            openai_update_meta_ajax();
            ob_end_clean();
        }
    }

    wp_reset_postdata();
    wp_send_json_success('Metadatos actualizados correctamente.');
}

add_action('wp_ajax_update_openai_meta_box', 'update_openai_meta_box_callback');

