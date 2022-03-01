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
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
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
     * Do staff upon plugin activation.
     *
     * @return void
     */
    public function activate() {
        
    }

    /**
     * Do staff upon plugin deactivation.
     *
     * @return void
     */
    public function deactivate() {

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
