<?php

namespace Asd\SimpleTodo\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use Asd\SimpleTodo\CRUD;

/**
 * TodosController API Class.
 */
class TodosController extends WP_REST_Controller {

    /**
     * Initialize the class.
     */
    public function __construct() {
        $this->namespace = 'wedevs/v1';
        $this->rest_base = 'todos';
    }


    /**
     * Registers the routes for the objects of the controller.
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                    'args'                => $this->get_collection_params(),
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],

                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args' => [
                    'id' => [
                        'description' => __( 'Unique identifier for the object.' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'get_item_permissions_check' ],
                    'args'                => [
                        'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'update_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'delete_item_permissions_check' ],
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );
    }

    /**
     * Checks if a given request has access to read data.
     *
     * @param \WP_REST_Request $request
     *
     * @return boolean
     */
    public function get_items_permissions_check( $request ) {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves a list of todo items.
     *
     * @since 1.0.0
     *
     * @param \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */
    public function get_items( $request ) {
        $args   = [];
        $params = $this->get_collection_params();

        foreach ( $params as $key => $value ) {
            if ( isset( $request[$key] ) ) {
                $args[$key] = $request[$key];
            }
        }

        $data  = [];
        $todos = CRUD::get_todo_tasks();

        foreach ( $todos as $todo ) {
            $response = $this->prepare_item_for_response( $todo, $request );
            $data[]   = $this->prepare_response_for_collection( $response );
        }

        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Get the todo, if ID is valid.
     *
     * @since 1.0.0
     *
     * @param int $id Supplied ID.
     *
     * @return Object|\WP_Error
     */
    protected function get_todo( $id ) {
        $todo = CRUD::get_todo_task( $id );

        if ( empty( $todo ) ) {
            return new WP_Error(
                'rest_todo_invalid_id',
                __( 'Invalid todo ID.' ),
                [ 'status' => 404 ]
            );
        }

        return $todo;
    }

    /**
     * Checks if a given request has access to get a specific item.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|bool
     */
    public function get_item_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves one item from the collection.
     *
     * @since 1.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_item( $request ) {
        $todo = $this->get_todo( $request['id'] );

        if ( is_wp_error( $todo ) ) {
            return $todo;
        }

        $response = $this->prepare_item_for_response( $todo, $request );
        $response = rest_ensure_response( $response );

        return $response;
    }

    /**
     * Checks if a given request has access to create items.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|bool
     */
    public function create_item_permissions_check( $request ) {
        return $this->get_items_permissions_check( $request );
    }

    /**
     * Creates one item to the collection.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|WP_REST_Response
     */
    public function create_item( $request ) {
        $todo = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $todo ) ) {
            return $todo;
        }

        $todo_id = CRUD::create_todo_task( $todo );

        if ( is_wp_error( $todo_id ) ) {
            $todo_id->add_data( [ 'status' => 400 ] );
        }

        $todo = $this->get_todo( $todo_id );
        $response = $this->prepare_item_for_response( $todo, $request );

        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $todo_id ) ) );

        return rest_ensure_response( $response );
    }

    /**
     * Checks if a given request has access to update a specific item.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_Error|bool
     */
    public function update_item_permissions_check( $request ) {
        return $this->get_item_permissions_check( $request );
    }

    /**
     * Updates one item from the collection.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|\WP_REST_Response
     */
    public function update_item( $request ) {
        $todo     = $this->get_todo( $request['id'] );
        $prepared = $this->prepare_item_for_database( $request );

        $prepared = array_merge( (array) $todo, $prepared );

        $updated = CRUD::update_todo_task( $prepared );

        if ( ! $updated ) {
            return new WP_Error(
                'rest_not_updated',
                __( 'Sorry, the todo could not be updated.' ),
                [ 'status' => 400 ]
            );
        }

        $todo     = $this->get_todo( $request['id'] );
        $response = $this->prepare_item_for_response( $todo, $request );

        return rest_ensure_response( $response );
    }

    /**
     * Checks if a given request has access to delete a specific item.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|bool
     */
    public function delete_item_permissions_check( $request ) {
        return $this->get_item_permissions_check( $request );
    }

    /**
     * Deletes one item from the collection.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|WP_REST_Response
     */
    public function delete_item( $request ) {
        $deleted = CRUD::delete_todo_task( $request['id'] );

        if ( ! $deleted ) {
            return new WP_Error(
                'rest_not_deleted',
                __( 'Sorry, the todo item could not be deleted.' ),
                [ 'status' => 400 ]
            );
        }

        $data = [
            'deleted'  => true,
        ];

        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Prepares one item for create or update operation.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|object
     */
    protected function prepare_item_for_database( $request ) {
        $prepared = [];

        if ( isset( $request['todo_name'] ) ) {
            $prepared['todo_name'] = $request['todo_name'];
        }

        if ( isset( $request['todo_status'] ) ) {
            $prepared['todo_status'] = $request['todo_status'];
        }

        return $prepared;
    }

    /**
     * Prepares the item for the REST response.
     *
     * @param mixed            $item    WordPress representation of the item.
     * @param \WP_REST_Request $request Request object.
     *
     * @return \WP_Error|object
     */
    public function prepare_item_for_response( $item, $request ) {
        $data   = [];
        $fields = $this->get_fields_for_response( $request );

        if ( in_array( 'id', $fields, true ) ) {
            $data['id'] = (int) $item->id;
        }

        if ( in_array( 'todo_name', $fields, true ) ) {
            $data['todo_name'] = (string) $item->todo_name;
        }

        if ( in_array( 'todo_status', $fields, true ) ) {
            $data['todo_status'] = (string) $item->todo_status;
        }

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->filter_response_by_context( $data, $context );

        $response = rest_ensure_response( $data );
        $response->add_links( $this->prepare_links( $item ) );

        return $response;
    }

    /**
     * Prepare links for the request.
     *
     * @param \WP_Post $post Post object.
     *
     * @return array Links for the given post.
     */
    protected function prepare_links( $item ) {
        $base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

        $links = [
            'self' => [
                'href' => rest_url( trailingslashit( $base ) . $item->id ),
            ],
            'collection' => [
                'href' => rest_url( $base ),
            ],
        ];

        return $links;
    }

    /**
     * Retrieves the contact schema, conforming to JSON Schema.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_item_schema() {
        if ( $this->schema ) {
            return $this->add_additional_fields_schema( $this->schema );
        }

        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'todo',
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'description' => __( 'Unique identifier for the object.' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'todo_name' => [
                    'description' => __( 'Name of the todo.' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'required'    => true,
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'todo_status' => [
                    'description' => __( 'Status of the todo.' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'required'    => true,
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ];

        $this->schema = $schema;

        return $this->add_additional_fields_schema( $this->schema );
    }

    /**
    * Retrieves the query params for collections.
    *
    * @since 1.0.0
    *
    * @return array
    */
   public function get_collection_params() {
       $params = parent::get_collection_params();

       unset( $params['search'] );
       unset( $params['page'] );
       unset( $params['per_page'] );

       return $params;
   }
}
