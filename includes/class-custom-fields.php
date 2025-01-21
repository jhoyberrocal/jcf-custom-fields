<?php
/**
 * Clase para manejar campos personalizados
 */
class JCF_Custom_Fields {

    private $fields = array();

    public function __construct() {
        $this->load_fields();
        add_action('add_meta_boxes', array($this, 'add_custom_meta_boxes'));
        add_action('save_post', array($this, 'save_custom_fields'));
        add_action('admin_init', array($this, 'add_taxonomy_fields'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_field_scripts'));
    }

    public function enqueue_field_scripts($hook) {
        // Lista de pantallas donde queremos cargar los scripts
        $valid_hooks = array('post.php', 'post-new.php', 'term.php', 'edit-tags.php');

        if (!in_array($hook, $valid_hooks)) {
            return;
        }

        // Cargar el script del repeater
        wp_enqueue_script(
            'jcf-repeater-script',
            JCF_PLUGIN_URL . 'assets/js/repeater.js',
            array('jquery'),
            JCF_VERSION,
            true
        );

        // Asegurarse de que los estilos estén cargados
        wp_enqueue_style(
            'jcf-admin-style',
            JCF_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JCF_VERSION
        );

        // Localizar el script para traducciones y variables
        wp_localize_script('jcf-repeater-script', 'jcf_repeater', array(
            'strings' => array(
                'add_item' => __('Añadir Item', 'jhoy-custom-fields'),
                'remove_item' => __('Eliminar', 'jhoy-custom-fields')
            )
        ));
    }

    private function load_fields() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jcf_fields';
        $results = $wpdb->get_results("SELECT * FROM {$table_name}");

        if ($results) {
            foreach ($results as $field) {
                // Asegurarnos de que las propiedades existan antes de usarlas
                $conditions = !empty($field->conditions) ? json_decode($field->conditions, true) : array();
                $options = !empty($field->options) ? json_decode($field->options, true) : array();

                $this->fields[$field->slug] = array(
                    'id' => $field->id,
                    'slug' => $field->slug,
                    'title' => $field->title,
                    'type' => $field->type,
                    'description' => !empty($field->description) ? $field->description : '',
                    'conditions' => $conditions,
                    'options' => $options
                );
            }
        }
    }

    public function add_taxonomy_fields() {
        $taxonomies = get_taxonomies(['public' => true]);
        foreach ($taxonomies as $taxonomy) {
            add_action($taxonomy . '_add_form_fields', array($this, 'render_taxonomy_fields'));
            add_action($taxonomy . '_edit_form_fields', array($this, 'render_taxonomy_fields'));
            add_action('create_' . $taxonomy, array($this, 'save_taxonomy_fields'));
            add_action('edited_' . $taxonomy, array($this, 'save_taxonomy_fields'));
        }
    }

    public function render_taxonomy_fields($term) {
        // Determinar si es una edición o creación
        $is_edit = is_object($term);
        $term_id = $is_edit ? $term->term_id : null;
        $taxonomy = $is_edit ? $term->taxonomy : $term;

        foreach ($this->fields as $field) {
            // Verificar que el campo esté habilitado para esta taxonomía
            if (!isset($field['conditions']['taxonomies']) ||
                !in_array($taxonomy, $field['conditions']['taxonomies'])) {
                continue;
            }

            $value = $term_id ? get_term_meta($term_id, '_jcf_' . $field['slug'], true) : '';

            // Renderizar método específico para formulario de creación
            if (!$is_edit) {
                echo '<div class="form-field">';
                echo '<label for="jcf_' . esc_attr($field['slug']) . '">' . esc_html($field['title']) . '</label>';
                $this->render_field($field, $value);
                echo '</div>';
            } else {
                // Renderizar método para formulario de edición
                echo '<tr class="form-field">';
                echo '<th scope="row">';
                echo '<label for="jcf_' . esc_attr($field['slug']) . '">' . esc_html($field['title']) . '</label>';
                echo '</th><td>';
                $this->render_field($field, $value);
                echo '</td></tr>';
            }
        }
    }

    private function render_field_wrapper($field, $value, $term = null) {
        $is_term_edit = is_object($term);
        if ($is_term_edit) {
            echo '<tr class="form-field">';
            echo '<th scope="row">';
            echo '<label for="jcf_' . esc_attr($field['slug']) . '">' . esc_html($field['title']) . '</label>';
            echo '</th><td>';
        } else {
            echo '<div class="form-field">';
            echo '<label for="jcf_' . esc_attr($field['slug']) . '">' . esc_html($field['title']) . '</label>';
        }

        $this->render_field($field, $value);

        if ($is_term_edit) {
            echo '</td></tr>';
        } else {
            echo '</div>';
        }
    }

    private function render_field($field, $value) {
        $field_class = $this->get_field_class($field['type']);
        if (!$field_class) {
            return '';
        }

        $field_instance = new $field_class($field, $value);
        echo $field_instance->render();
    }

    private function get_field_class($type) {
        $field_classes = array(
            'text' => 'JCF_Field_Text',
            'textarea' => 'JCF_Field_Textarea',
            'select' => 'JCF_Field_Select',
            'number' => 'JCF_Field_Number',
            'color' => 'JCF_Field_Color',
            'date' => 'JCF_Field_Date',
            'repeater' => 'JCF_Field_Repeater',
        );

        return isset($field_classes[$type]) ? $field_classes[$type] : 'JCF_Field_Text';
    }

    public function add_custom_meta_boxes() {
        $post_types = get_post_types(['public' => true]);

        foreach ($post_types as $post_type) {
            $has_fields = false;
            foreach ($this->fields as $field) {
                if (isset($field['conditions']['post_types']) &&
                    in_array($post_type, $field['conditions']['post_types'])) {
                    $has_fields = true;
                    break;
                }
            }

            if ($has_fields) {
                add_meta_box(
                    'jcf_custom_fields',
                    __('JCF Meta', 'jhoy-custom-fields'),
                    array($this, 'render_post_fields'),
                    $post_type,
                    'side',
                    'default'
                );
            }
        }
    }

    public function render_post_fields($post) {
        wp_nonce_field('jcf_custom_fields_nonce', 'jcf_custom_fields_nonce');

        foreach ($this->fields as $field) {
            if (!isset($field['conditions']['post_types']) ||
                !in_array($post->post_type, $field['conditions']['post_types'])) {
                continue;
            }

            $value = get_post_meta($post->ID, '_jcf_' . $field['slug'], true);
            $this->render_field_wrapper($field, $value);
        }
    }

    public function save_custom_fields($post_id) {
        if (!isset($_POST['jcf_custom_fields_nonce']) ||
            !wp_verify_nonce($_POST['jcf_custom_fields_nonce'], 'jcf_custom_fields_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        foreach ($this->fields as $field) {
            $field_name = 'jcf_' . $field['slug'];

            // Si el campo existe en el POST
            if (isset($_POST[$field_name])) {
                $value = $_POST[$field_name];

                // Manejo especial para campos repeater
                if ($field['type'] === 'repeater') {
                    // Decodificar el JSON si es una cadena JSON válida
                    $decoded_value = json_decode(wp_unslash($value), true);

                    if (is_array($decoded_value)) {
                        // Filtrar valores vacíos y sanitizar
                        $decoded_value = array_filter($decoded_value, function($item) {
                            return !empty(trim($item));
                        });

                        $sanitized_value = array_map('sanitize_text_field', $decoded_value);

                        // Guardar como JSON si hay valores, o como array vacío si no hay
                        $value_to_save = !empty($sanitized_value) ?
                            wp_json_encode($sanitized_value) :
                            wp_json_encode([]);
                    } else {
                        // Si no es JSON válido, guardar como array vacío
                        $value_to_save = wp_json_encode([]);
                    }

                    update_post_meta($post_id, '_' . $field_name, $value_to_save);

                    // Debug - Guardar en el log de WordPress
                    error_log('Guardando repeater field: ' . $field_name);
                    error_log('Valor guardado: ' . $value_to_save);
                } else {
                    // Para otros tipos de campos
                    update_post_meta(
                        $post_id,
                        '_' . $field_name,
                        sanitize_text_field($value)
                    );
                }
            }
        }
    }

    public function save_taxonomy_fields($term_id) {
        foreach ($this->fields as $field) {
            $field_name = 'jcf_' . $field['slug'];
            if (isset($_POST[$field_name])) {
                update_term_meta(
                    $term_id,
                    '_' . $field_name,
                    sanitize_text_field($_POST[$field_name])
                );
            }
        }
    }

    public static function get_field($field_slug, $post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        return get_post_meta($post_id, '_jcf_' . $field_slug, true);
    }
}

// Función global para obtener campos
if (!function_exists('get_field')) {
    function get_field($field_slug, $post_id = null) {
        return JCF_Custom_Fields::get_field($field_slug, $post_id);
    }
}
