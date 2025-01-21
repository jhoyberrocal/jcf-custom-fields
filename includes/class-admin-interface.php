<?php
/**
 * Clase para manejar la interfaz administrativa
 */
class JCF_Admin_Interface {

    private $current_tab = 'fields';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('admin_init', array($this, 'handle_form_submission'));
        add_action('wp_ajax_jcf_get_field', array($this, 'ajax_get_field'));
        add_action('wp_ajax_jcf_delete_field', array($this, 'ajax_delete_field'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function add_menu_pages() {
        add_menu_page(
            __('Jhoy Custom Fields', 'jhoy-custom-fields'),
            __('JCF', 'jhoy-custom-fields'),
            'manage_options',
            'jhoy-custom-fields',
            array($this, 'render_admin_page'),
            'dashicons-layout',
            30
        );
    }

    public function enqueue_scripts($hook) {
        if ('toplevel_page_jhoy-custom-fields' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'jcf-admin-style',
            JCF_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JCF_VERSION
        );

        // Script principal de administración
        wp_enqueue_script(
            'jcf-admin-script',
            JCF_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            JCF_VERSION,
            true
        );

        wp_enqueue_script(
            'jcf-repeater-script',
            JCF_PLUGIN_URL . 'assets/js/repeater.js',
            array('jquery', 'jcf-admin-script'),
            JCF_VERSION,
            true
        );

        // Script específico para taxonomías
        if (isset($_GET['tab']) && $_GET['tab'] === 'taxonomies') {
            wp_enqueue_script(
                'jcf-taxonomy-admin-script',
                JCF_PLUGIN_URL . 'assets/js/taxonomy-admin.js',
                array('jquery'),
                JCF_VERSION,
                true
            );
        }

        wp_localize_script('jcf-admin-script', 'jcf_admin', array(
            'nonce' => wp_create_nonce('jcf_nonce'),
            'strings' => array(
                'confirm_delete' => __('¿Estás seguro de que quieres eliminar este campo?', 'jhoy-custom-fields'),
                'confirm_delete_taxonomy' => __('¿Estás seguro de que quieres eliminar esta taxonomía?', 'jhoy-custom-fields'),
                'error_loading' => __('Error al cargar', 'jhoy-custom-fields'),
                'error_deleting' => __('Error al eliminar', 'jhoy-custom-fields'),
                'select_post_type' => __('Debes seleccionar al menos un tipo de contenido', 'jhoy-custom-fields'),
                'add_item' => __('Añadir Item', 'jhoy-custom-fields'),
                'remove_item' => __('Eliminar', 'jhoy-custom-fields')
            )
        ));
    }

    public function render_admin_page() {
        // Determinar la pestaña actual
        $this->current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'fields';

        // Obtener datos según la pestaña
        global $wpdb;

        if ($this->current_tab === 'fields') {
            $table_name = $wpdb->prefix . 'jcf_fields';
            $fields = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id DESC");

            if ($fields) {
                foreach ($fields as $field) {
                    $field->conditions = !empty($field->conditions) ? $field->conditions : '';
                    $field->options = !empty($field->options) ? $field->options : '';
                    $field->description = !empty($field->description) ? $field->description : '';
                }
            }
        } else if ($this->current_tab === 'taxonomies') {
            $table_name = $wpdb->prefix . 'jcf_taxonomies';
            $taxonomies = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id DESC");
        }

        // Cargar la vista principal
        include JCF_PLUGIN_DIR . 'includes/views/admin-page.php';
    }

    public function ajax_get_field() {
        check_ajax_referer('jcf_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('No tienes permisos suficientes', 'jhoy-custom-fields'));
        }

        $field_id = isset($_POST['field_id']) ? intval($_POST['field_id']) : 0;

        if (!$field_id) {
            wp_send_json_error(__('ID de campo no válido', 'jhoy-custom-fields'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'jcf_fields';
        $field = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $field_id
        ));

        if (!$field) {
            wp_send_json_error(__('Campo no encontrado', 'jhoy-custom-fields'));
        }

        wp_send_json_success($field);
    }

    public function ajax_delete_field() {
        check_ajax_referer('jcf_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('No tienes permisos suficientes', 'jhoy-custom-fields'));
        }

        $field_id = isset($_POST['field_id']) ? intval($_POST['field_id']) : 0;

        if (!$field_id) {
            wp_send_json_error(__('ID de campo no válido', 'jhoy-custom-fields'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'jcf_fields';
        $result = $wpdb->delete(
            $table_name,
            array('id' => $field_id),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(__('Error al eliminar el campo', 'jhoy-custom-fields'));
        }

        wp_send_json_success(__('Campo eliminado correctamente', 'jhoy-custom-fields'));
    }

    public function handle_form_submission() {
        // Verificar si es una petición de guardado de campo
        if (!isset($_POST['action']) || $_POST['action'] !== 'save_field') {
            return;
        }

        // Verificar nonce
        if (!isset($_POST['jcf_field_nonce']) ||
            !wp_verify_nonce($_POST['jcf_field_nonce'], 'jcf_save_field')) {
            wp_die(__('Verificación de seguridad fallida', 'jhoy-custom-fields'));
        }

        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes', 'jhoy-custom-fields'));
        }

        // Validar campos requeridos
        if (empty($_POST['field_title']) || empty($_POST['field_slug']) || empty($_POST['field_type'])) {
            wp_die(__('Por favor, completa todos los campos requeridos', 'jhoy-custom-fields'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'jcf_fields';

        $options = [];
        if ($_POST['field_type'] === 'select' &&
            isset($_POST['field_options']['values']) &&
            isset($_POST['field_options']['labels'])) {

            $values = array_map('sanitize_text_field', $_POST['field_options']['values']);
            $labels = array_map('sanitize_text_field', $_POST['field_options']['labels']);

            // Combinar valores y etiquetas en un array asociativo
            $options = array_combine($values, $labels);
        }

        // Preparar datos
        $data = array(
            'title' => sanitize_text_field($_POST['field_title']),
            'slug' => sanitize_key($_POST['field_slug']),
            'type' => sanitize_key($_POST['field_type']),
            'description' => isset($_POST['field_description']) ?
                sanitize_textarea_field($_POST['field_description']) : '',
            'conditions' => json_encode(array(
                'post_types' => isset($_POST['field_conditions']['post_types']) ?
                    array_map('sanitize_key', $_POST['field_conditions']['post_types']) : array(),
                'taxonomies' => isset($_POST['field_conditions']['taxonomies']) ?
                    array_map('sanitize_key', $_POST['field_conditions']['taxonomies']) : array()
            )),
            'options' => json_encode($options)
        );

        $format = array(
            '%s', // title
            '%s', // slug
            '%s', // type
            '%s', // description
            '%s', // conditions
            '%s'  // options
        );

        // Determinar si es una actualización o inserción
        $field_id = !empty($_POST['field_id']) ? intval($_POST['field_id']) : 0;

        try {
            if ($field_id > 0) {
                // Actualización
                $where = array('id' => $field_id);
                $where_format = array('%d');

                // No permitir cambiar el slug en edición
                unset($data['slug']);
                array_splice($format, 1, 1);

                $result = $wpdb->update($table_name, $data, $where, $format, $where_format);
            } else {
                // Inserción
                // Verificar que el slug no existe
                $existing_slug = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_name WHERE slug = %s",
                    $data['slug']
                ));

                if ($existing_slug) {
                    wp_die(__('Ya existe un campo con ese slug', 'jhoy-custom-fields'));
                }

                $result = $wpdb->insert($table_name, $data, $format);
            }

            if ($result === false) {
                throw new Exception($wpdb->last_error);
            }

            // Redirigir con mensaje de éxito
            wp_redirect(add_query_arg(
                array(
                    'page' => 'jhoy-custom-fields',
                    'message' => 'saved'
                ),
                admin_url('admin.php')
            ));
            exit;

        } catch (Exception $e) {
            wp_die(sprintf(
                __('Error al guardar el campo: %s', 'jhoy-custom-fields'),
                $e->getMessage()
            ));
        }
    }
}
