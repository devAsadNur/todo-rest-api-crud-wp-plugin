<?php
/**
 * Plugin Name: Simple Todo
 * Plugin URI:  https://asadnur.dev/
 * Description: Plugin description
 * Version:     1.0.0
 * Author:      Asad Nur
 * Author URI:  https://asadnur.dev/
 * Text Domain: asd-simple-todo
 * License:     GPL2
 */

/**
 * Don't call the file directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class.
 */
final class AsdSimpleTodo {

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->define_constants();

        register_activation_hook( __FILE__, [ $this, 'activate' ] );

        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
    }

    /**
     * Initialize a singleton instance.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define plugin constants.
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'ASD_SIMPLE_TODO_FILE', __FILE__ );
        define( 'ASD_SIMPLE_TODO_PATH', __DIR__ );
        define( 'ASD_SIMPLE_TODO_URL', plugins_url( '', ASD_SIMPLE_TODO_FILE ) );
        define( 'ASD_SIMPLE_TODO_ASSETS', ASD_SIMPLE_TODO_URL . '/assets' );
    }

    /**
     * Initialize the plugin.
     *
     * @return void
     */
    public function init_plugin() {
        require_once( ASD_SIMPLE_TODO_PATH . '/includes/API.php' );

        new Asd\SimpleTodo\API();
    }

    /**
     * Do staff upon plugin activation.
     *
     * @return void
     */
    public function activate() {
        $this->create_db_tables();
    }

    /**
     * Create DB tables.
     *
     * @return void
     */
    public function create_db_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $schema_todo_lists = "CREATE TABLE `{$wpdb->prefix}asd_simple_todo_lists` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `todo_name` varchar(255) NOT NULL,
            `todo_status` text NOT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;";

        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta( $schema_todo_lists );
    }
}

/**
 * Initialize the main plugin class.
 */
function asd_simple_todo() {
    return AsdSimpleTodo::init();
}

/**
 * Kick-off the plugin.
 */
asd_simple_todo();
