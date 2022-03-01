<?php

namespace Asd\SimpleTodo;

use WP_Error;

/**
 * CRUD Class.
 */
class CRUD {

    /**
     * Create todo task.
     *
     * @param array $args
     *
     * @return int|\WP_Error
     */
    public static function create_todo_task( $args=[] ) {
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

    /**
     * Update todo task.
     *
     * @param array $args
     *
     * @return int|\WP_Error
     */
    public static function update_todo_task( $args=[] ) {
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

    /**
     * Gets todo tasks.
     *
     * @return array|null
     */
    public static function get_todo_tasks() {
        global $wpdb;

        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}asd_simple_todo_lists" );

        return $results;
    }

    /**
     * Gets single todo task by ID.
     *
     * @param int $id
     *
     * @return object|null
     */
    public static function get_todo_task( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}asd_simple_todo_lists WHERE id = %d", $id )
        );
    }

    /**
     * Deletes todo task by ID.
     *
     * @param int $id
     *
     * @return int|boolean
     */
    public static function delete_todo_task( $id ) {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'asd_simple_todo_lists',
            [ 'id' => $id ],
            [ '%d' ]
        );
    }
}
