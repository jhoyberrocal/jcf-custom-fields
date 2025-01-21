<?php
/**
 * Clase para manejar taxonomías personalizadas
 */
class JCF_Custom_Taxonomies {

    private $taxonomies = array();

    public function __construct() {
        add_action('init', array($this, 'register_custom_taxonomies'), 20);  // Prioridad más baja
        add_action('wp_ajax_jcf_get_taxonomy', array($this, 'ajax_get_taxonomy'));
        add_action('wp_ajax_jcf_delete_taxonomy', array($this, 'ajax_delete_taxonomy'));
        add_action('admin_init', array($this, 'handle_form_submission'));
        $this->load_taxonomies();
    }

    private function load_taxonomies() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jcf_taxonomies';
        $results = $wpdb->get_results("SELECT * FROM {$table_name}");

        if ($results) {
            foreach ($results as $taxonomy) {
                // Parsear post_types si es un string JSON
                if (is_string($taxonomy->post_types)) {
                    $taxonomy->post_types = json_decode($taxonomy->post_types, true);
                }

                // Asegurar que sea un array
                if (!is_array($taxonomy->post_types)) {
                    $taxonomy->post_types = array('post');
                }

                $this->taxonomies[$taxonomy->slug] = $taxonomy;
            }
        }
    }

    public function register_custom_taxonomies() {
        foreach ($this->taxonomies as $taxonomy) {
            // Manejar diferentes tipos de datos para post_types
            $post_types = null;

            // Si es un string JSON
            if (is_string($taxonomy->post_types)) {
                $post_types = json_decode($taxonomy->post_types, true);
            }
            // Si ya es un array
            elseif (is_array($taxonomy->post_types)) {
                $post_types = $taxonomy->post_types;
            }

            // Si no hay post types, usar post por defecto
            if (empty($post_types)) {
                $post_types = array('post');
            }

            $labels = array(
                'name'              => $taxonomy->name,
                'singular_name'     => $taxonomy->name,
                'search_items'      => sprintf(__('Buscar %s', 'jhoy-custom-fields'), $taxonomy->name),
                'all_items'         => sprintf(__('Todas las %s', 'jhoy-custom-fields'), $taxonomy->name),
                'parent_item'       => sprintf(__('%s superior', 'jhoy-custom-fields'), $taxonomy->name),
                'parent_item_colon' => sprintf(__('%s superior:', 'jhoy-custom-fields'), $taxonomy->name),
                'edit_item'         => sprintf(__('Editar %s', 'jhoy-custom-fields'), $taxonomy->name),
                'update_item'       => sprintf(__('Actualizar %s', 'jhoy-custom-fields'), $taxonomy->name),
                'add_new_item'      => sprintf(__('Añadir nueva %s', 'jhoy-custom-fields'), $taxonomy->name),
                'new_item_name'     => sprintf(__('Nuevo nombre de %s', 'jhoy-custom-fields'), $taxonomy->name),
                'menu_name'         => $taxonomy->name,
            );

            $args = array(
                'hierarchical'      => (bool) $taxonomy->hierarchical,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array('slug' => $taxonomy->slug),
                'show_in_menu'      => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'publicly_queryable' => true,
                'public'            => true
            );

            register_taxonomy($taxonomy->slug, $post_types, $args);
        }
    }

    public function ajax_get_taxonomy() {
        check_ajax_referer('jcf_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('No tienes permisos suficientes', 'jhoy-custom-fields'));
        }

        $taxonomy_id = isset($_POST['taxonomy_id']) ? intval($_POST['taxonomy_id']) : 0;

        if (!$taxonomy_id) {
            wp_send_json_error(__('ID de taxonomía no válido', 'jhoy-custom-fields'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'jcf_taxonomies';
        $taxonomy = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $taxonomy_id
        ));

        if (!$taxonomy) {
            wp_send_json_error(__('Taxonomía no encontrada', 'jhoy-custom-fields'));
        }

        wp_send_json_success($taxonomy);
    }

    public function ajax_delete_taxonomy() {
        check_ajax_referer('jcf_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('No tienes permisos suficientes', 'jhoy-custom-fields'));
        }

        $taxonomy_id = isset($_POST['taxonomy_id']) ? intval($_POST['taxonomy_id']) : 0;

        if (!$taxonomy_id) {
            wp_send_json_error(__('ID de taxonomía no válido', 'jhoy-custom-fields'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'jcf_taxonomies';

        // Obtener el slug antes de eliminar
        $taxonomy = $wpdb->get_row($wpdb->prepare(
            "SELECT slug FROM $table_name WHERE id = %d",
            $taxonomy_id
        ));

        if ($taxonomy) {
            // Eliminar la taxonomía de la base de datos
            $result = $wpdb->delete(
                $table_name,
                array('id' => $taxonomy_id),
                array('%d')
            );

            if ($result === false) {
                wp_send_json_error(__('Error al eliminar la taxonomía', 'jhoy-custom-fields'));
            }

            // Limpiar el caché de términos
            clean_term_cache(array(), $taxonomy->slug);
            delete_option($taxonomy->slug . '_children');

            wp_send_json_success(__('Taxonomía eliminada correctamente', 'jhoy-custom-fields'));
        }

        wp_send_json_error(__('Taxonomía no encontrada', 'jhoy-custom-fields'));
    }

    public function handle_form_submission() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'save_taxonomy') {
            return;
        }

        if (!isset($_POST['jcf_taxonomy_nonce']) ||
            !wp_verify_nonce($_POST['jcf_taxonomy_nonce'], 'jcf_save_taxonomy')) {
            wp_die(__('Verificación de seguridad fallida', 'jhoy-custom-fields'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes', 'jhoy-custom-fields'));
        }

        if (empty($_POST['taxonomy_name']) || empty($_POST['taxonomy_slug'])) {
            wp_die(__('Por favor, completa todos los campos requeridos', 'jhoy-custom-fields'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'jcf_taxonomies';

        $data = array(
            'name' => sanitize_text_field($_POST['taxonomy_name']),
            'slug' => sanitize_key($_POST['taxonomy_slug']),
            'description' => isset($_POST['taxonomy_description']) ?
                sanitize_textarea_field($_POST['taxonomy_description']) : '',
            'hierarchical' => isset($_POST['taxonomy_hierarchical']) ? 1 : 0,
            'post_types' => is_array($_POST['taxonomy_post_types']) ?
                json_encode(array_map('sanitize_key', $_POST['taxonomy_post_types'])) : '[]'
        );

        $format = array('%s', '%s', '%s', '%d', '%s');

        $taxonomy_id = !empty($_POST['taxonomy_id']) ? intval($_POST['taxonomy_id']) : 0;

        try {
            if ($taxonomy_id > 0) {
                // Actualización
                $where = array('id' => $taxonomy_id);
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
                    wp_die(__('Ya existe una taxonomía con ese slug', 'jhoy-custom-fields'));
                }

                $result = $wpdb->insert($table_name, $data, $format);
            }

            if ($result === false) {
                throw new Exception($wpdb->last_error);
            }

            flush_rewrite_rules();

            wp_redirect(add_query_arg(
                array(
                    'page' => 'jhoy-custom-fields',
                    'tab' => 'taxonomies',
                    'message' => 'saved'
                ),
                admin_url('admin.php')
            ));
            exit;

        } catch (Exception $e) {
            wp_die(sprintf(
                __('Error al guardar la taxonomía: %s', 'jhoy-custom-fields'),
                $e->getMessage()
            ));
        }
    }
}
