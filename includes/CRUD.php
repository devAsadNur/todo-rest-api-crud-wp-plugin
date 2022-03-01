<?php

namespace Asd\SimpleTodo;

use WP_Error;

/**
 * CRUD Class.
 */
class CRUD {

    public function __construct() {
        // add_action( 'admin_footer', [ $this, 'test' ] );
    }

    public function test() {
        // $args = [
        //     'id' => 3,
        //     'todo_name' => 'Todo 3',
        //     'todo_status' => 'in-progress',
        // ];

        $results = $this->get_todo_tasks();
        error_log( print_r( $results, 1 ) );
    }

    public function create_todo_task( $args=[] ) {
        global $wpdb;

        if ( empty( $args['todo_name'] ) ) {
            return new WP_Error( 'no-todo-name', __( 'Todo name can\'t be empty', 'asd-simple-todo' ) );
        }

        $defaults = [
            'todo_name'   => '',
            'todo_status' => 'to-do',
        ];

        $data = wp_parse_args( $args, $defaults );

        $inserted = $wpdb->insert(
            $wpdb->prefix . "asd_simple_todo_lists",
            $data,
            [
                '%s',
                '%s',
            ]
        );

        if ( ! $inserted ) {
            return new WP_Error( 'failed-to-insert', __( 'Failed to insert todo item', 'asd-simple-todo' ) );
        }

        return $wpdb->insert_id;
    }

    public function update_todo_task( $args=[] ) {
        global $wpdb;

        $todo_id = $args['id'];

        if ( empty( $todo_id ) ) {
            return new WP_Error( 'no-todo-id', __( 'Todo ID can\'t be empty', 'asd-simple-todo' ) );
        }

        if ( empty( $args['todo_name'] ) ) {
            return new WP_Error( 'no-todo-name', __( 'Todo name can\'t be empty', 'asd-simple-todo' ) );
        }

        if ( empty( $args['todo_status'] ) ) {
            return new WP_Error( 'no-todo-status', __( 'Todo status can\'t be empty', 'asd-simple-todo' ) );
        }

        $defaults = [
            'todo_name'   => '',
            'todo_status' => 'to-do',
        ];

        unset( $args['id'] );

        $data = wp_parse_args( $args, $defaults );

        $updated = $wpdb->update(
            $wpdb->prefix . 'asd_simple_todo_lists',
            $data,
            [ 'id' => $todo_id ],
            [
                '%s',
                '%s',
            ],
            [ '%d' ]
        );

        if ( ! $updated ) {
            return new WP_Error( 'failed-to-update', __( 'Failed to update todo item', 'asd-simple-todo' ) );
        }

        return $updated;
    }

    public function get_todo_tasks() {
        global $wpdb;

        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}asd_simple_todo_lists", ARRAY_A );

        return $results;
    }

    public function get_todo_task( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}asd_simple_todo_lists WHERE id = %d", $id )
        );
    }

    public function delete_todo_task( $id ) {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'asd_simple_todo_lists',
            [ 'id' => $id ],
            [ '%d' ]
        );
    }
}
