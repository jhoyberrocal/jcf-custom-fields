<?php
/**
 * Plugin Name: Jhoy Custom Fields
 * Plugin URI:
 * Description: Plugin para gestionar campos personalizados
 * Version: 1.0.0
 * Author: Jhoy
 * Author URI:
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jhoy-custom-fields
 */

if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes al inicio del archivo
define('JCF_VERSION', '1.0.0');
define('JCF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JCF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JCF_PLUGIN_FILE', __FILE__);

// Función de activación del plugin
function jcf_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Tabla de taxonomías personalizadas
    $taxonomies_table = $wpdb->prefix . 'jcf_taxonomies';

    try {
        // Crear tabla de taxonomías
        $sql = "CREATE TABLE IF NOT EXISTS $taxonomies_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            hierarchical tinyint(1) DEFAULT 0,
            post_types text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = $wpdb->query($sql);

        if ($result === false) {
            error_log('Error creating taxonomies table: ' . $wpdb->last_error);
            throw new Exception('Error creating taxonomies table: ' . $wpdb->last_error);
        }

        // Tabla de campos personalizados
        $fields_table = $wpdb->prefix . 'jcf_fields';
        $sql = "CREATE TABLE IF NOT EXISTS $fields_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            type varchar(50) NOT NULL,
            description text,
            conditions text,
            options text,
            required tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";

        $result = $wpdb->query($sql);

        if ($result === false) {
            error_log('Error creating fields table: ' . $wpdb->last_error);
            throw new Exception('Error creating fields table: ' . $wpdb->last_error);
        }

        // Guardar versión en opciones
        update_option('jcf_db_version', '1.0');

    } catch (Exception $e) {
        error_log('JCF Plugin activation error: ' . $e->getMessage());
        wp_die('Error activating plugin: ' . $e->getMessage());
    }

    flush_rewrite_rules();
}

// Registrar función de activación
register_activation_hook(__FILE__, 'jcf_activate');

// Autoloader para las clases del plugin
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'JCF_') === 0) {
        $class_name = str_replace('JCF_', '', $class_name);

        // Manejo especial para las clases de campos
        if (strpos($class_name, 'Field_') === 0) {
            $file = JCF_PLUGIN_DIR . 'includes/fields/class-' .
                strtolower(str_replace('_', '-', $class_name)) . '.php';
        } else {
            $class_name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name));
            $file = JCF_PLUGIN_DIR . 'includes/class-' . $class_name . '.php';
        }

        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

class Jhoy_Custom_Fields {
    private static $instance = null;
    private $custom_fields;
    private $custom_taxonomies;
    private $admin_interface;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'check_tables_exist'));
    }

    public function check_tables_exist() {
        global $wpdb;
        $taxonomies_table = $wpdb->prefix . 'jcf_taxonomies';
        $fields_table = $wpdb->prefix . 'jcf_fields';

        if ($wpdb->get_var("SHOW TABLES LIKE '$taxonomies_table'") != $taxonomies_table ||
            $wpdb->get_var("SHOW TABLES LIKE '$fields_table'") != $fields_table) {

            error_log('JCF tables do not exist, attempting to create them...');
            jcf_activate();
        }
    }

    public function init() {
        $this->load_dependencies();

        if (is_admin()) {
            $this->admin_interface = new JCF_Admin_Interface();
        }

        $this->custom_fields = new JCF_Custom_Fields();
        $this->custom_taxonomies = new JCF_Custom_Taxonomies();
    }

    private function load_dependencies() {
        $required_files = array(
            'class-custom-fields.php',
            'class-custom-taxonomies.php',
            'class-admin-interface.php'
        );

        foreach ($required_files as $file) {
            $file_path = JCF_PLUGIN_DIR . 'includes/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log('JCF required file not found: ' . $file_path);
                wp_die(sprintf('El archivo %s no existe', $file));
            }
        }
    }
}

// Inicializar el plugin
add_action('plugins_loaded', function() {
    Jhoy_Custom_Fields::get_instance();
});