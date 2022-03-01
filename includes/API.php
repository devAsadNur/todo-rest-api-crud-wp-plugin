<?php

namespace Asd\SimpleTodo;

/**
 * API Class.
 */
class API {

    /**
     * Class constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_api' ] );
    }

    /**
     * Register REST API.
     *
     * @return void
     */
    public function register_api() {
        require_once( ASD_SIMPLE_TODO_PATH . '/includes/API/TodosController.php' );

        $todos = new API\TodosController();
        $todos->register_routes();
    }
}
