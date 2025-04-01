<?php

namespace Netdust\Service\Posts;


use Netdust\Logger\Logger;
use Netdust\Logger\LoggerInterface;
use Netdust\Traits\Features;
use Netdust\Traits\Setters;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Post {

    /**
     * The post type.
     *
     * @since 1.0.0
     *
     * @var string The post type "$type" argument.
     */
    protected string $type = '';

    /**
     * The post type args.
     *
     * @since 1.0.0
     *
     * @var array The list of post type args. See https://developer.wordpress.org/reference/functions/register_post_type/
     */
    protected array $args = [];

    /**
     * Custom_Post_Type_Instance constructor.
     *
     * @param array $args Overrides to default args in the object
     */
    public function __construct( string $type, array $args = [] ) {
        $this->type = $type;
        $this->args = array_merge($args, [
            //'capability_type' => ['post']
        ]);
    }

    /**
     * @inheritDoc
     */
    public function do_actions(): void {
        add_action( 'init', [ $this, 'register' ] );

        add_filter( 'rest_' . $this->type . '_query', [ $this, 'rest_query' ], 10, 2 );


        add_filter( 'post_updated_messages', array( $this, 'messages' ) );
        add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_messages' ), 10, 2 );
        add_filter( 'manage_edit-' . $this->type . '_columns', array( $this, 'columns' ) );
        add_filter( 'manage_edit-' . $this->type . '_sortable_columns', array( $this, 'sortable_columns' ) );
        // Different column registration for pages/posts
        $h = isset( $this->args['hierarchical'] ) && $this->args['hierarchical'] ? 'pages' : 'posts';
        add_action( "manage_{$h}_custom_column", array( $this, 'columns_display' ), 10, 2 );
        add_filter( 'enter_title_here', array( $this, 'change_title_text' ) );
    }

    /**
     * Updates REST Requests to use prepared query arguments for REST Requests.
     *
     * @param array            $args
     * @param \WP_REST_Request $request
     *
     * @return array
     *@since 1.0.0
     *
     */
    public function rest_query( array $args, \WP_REST_Request $request ): array {
        return $this->prepare_query_args( $args );
    }

    /**
     * Registers the post type.
     *
     * @since 1.0.0
     */
    public function register(): bool|\WP_Error {

        $this->args['labels'] = $this->create_labels( );

        $registered = register_post_type( $this->type, $this->args );

        if ( is_wp_error( $registered ) ) {
	        return app()->make( LoggerInterface::class )->error( $registered->get_error_message(), $registered->get_error_code(), $registered->get_error_data() );
        } else {

	        app()->make( LoggerInterface::class )->info(
                'The custom post type ' . $this->type . ' has been registered.',
                'custom_post_type_registered'
            );
        }

		return true;
    }

    public function columns( $columns ) {
        // placeholder
        return $columns;
    }

    public function sortable_columns( $sortable_columns ) {
        // placeholder
        return $sortable_columns;
    }

    public function columns_display( $column, $post_id ) {
        // placeholder
    }

    public function change_title_text( string $title ): string {
        $screen = get_current_screen();

        if  ( $this->type == $screen->post_type ) {
            return sprintf( __( '%s Title', app()->text_domain ), $this->args['labels']['singular_name'] );
        }
        return $title;
    }

    /**
     * Run a WP_Query against this post type.
     *
     * @param array $args Query arguments to provide.
     *
     * @return \WP_Query The WP Query object.
     *@since 1.0.0
     *
     */
    public function query( array $args = [] ): \WP_Query {
        return new \WP_Query( $this->prepare_query_args( $args ) );
    }

    /**
     * Prepares query args specific to this post type.
     *
     * @since 1.0.o
     *
     * @param array $args Post args to process.
     *
     * @return array Processed query arguments.
     */
    protected function prepare_query_args( array $args ): array {
        // placeholder
        return $args;
    }

    /**
     * Modifies CPT based messages to include our CPT labels
     * @since  0.1.0
     * @param  array  $messages Array of messages
     * @return array            Modified messages array
     */
    public function messages( $messages ) {
        global $post, $post_ID;

        $cpt_messages = array(
            0 => '', // Unused. Messages start at index 1.
            2 => __( 'Custom field updated.' ),
            3 => __( 'Custom field deleted.' ),
            4 => sprintf( __( '%1$s updated.', app()->text_domain ), $this->args['labels']['singular_name'] ),
            /* translators: %s: date and time of the revision */
            5 => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', app()->text_domain ), $this->args['labels']['singular_name'] , wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            7 => sprintf( __( '%1$s saved.', app()->text_domain ), $this->args['labels']['singular_name'] ),
        );

        if ( $this->args[ 'public' ] ) {

            $cpt_messages[1] = sprintf( __( '%1$s updated. <a href="%2$s">View %1$s</a>', app()->text_domain ), $this->args['labels']['singular_name'], esc_url( get_permalink( $post_ID ) ) );
            $cpt_messages[6] = sprintf( __( '%1$s published. <a href="%2$s">View %1$s</a>', app()->text_domain ), $this->args['labels']['singular_name'], esc_url( get_permalink( $post_ID ) ) );
            $cpt_messages[8] = sprintf( __( '%1$s submitted. <a target="_blank" href="%2$s">Preview %1$s</a>', app()->text_domain ), $this->args['labels']['singular_name'], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );
            // translators: Publish box date format, see http://php.net/date
            $cpt_messages[9] = sprintf( __( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%3$s">Preview %1$s</a>', app()->text_domain ), $this->args['labels']['singular_name'], date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) );
            $cpt_messages[10] = sprintf( __( '%1$s draft updated. <a target="_blank" href="%2$s">Preview %1$s</a>', app()->text_domain ), $this->args['labels']['singular_name'], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );

        } else {

            $cpt_messages[1] = sprintf( __( '%1$s updated.', app()->text_domain ), $this->args['labels']['singular_name'] );
            $cpt_messages[6] = sprintf( __( '%1$s published.', app()->text_domain ), $this->args['labels']['singular_name'] );
            $cpt_messages[8] = sprintf( __( '%1$s submitted.', app()->text_domain ), $this->args['labels']['singular_name'] );
            // translators: Publish box date format, see http://php.net/date
            $cpt_messages[9] = sprintf( __( '%1$s scheduled for: <strong>%2$s</strong>.', app()->text_domain ), $this->args['labels']['singular_name'], date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) );
            $cpt_messages[10] = sprintf( __( '%1$s draft updated.', app()->text_domain ), $this->args['labels']['singular_name'] );

        }

        $messages[ $this->type ] = $cpt_messages;
        return $messages;
    }

    /**
     * Custom bulk actions messages for this post type
     * @author	Neil Lowden
     *
     * @param  array  $bulk_messages  Array of messages
     * @param  array  $bulk_counts    Array of counts under keys 'updated', 'locked', 'deleted', 'trashed' and 'untrashed'
     * @return array                  Modified array of messages
     */
    function bulk_messages( $bulk_messages, $bulk_counts ) {
        $bulk_messages[ $this->type ] = array(
            'updated'   => sprintf( _n( '%1$s %2$s updated.', '%1$s %3$s updated.', $bulk_counts['updated'], app()->text_domain ), $bulk_counts['updated'], $this->args['labels']['singular_name'], $this->args['labels']['name'] ),
            'locked'    => sprintf( _n( '%1$s %2$s not updated, somebody is editing it.', '%1$s %3$s not updated, somebody is editing them.', $bulk_counts['locked'], app()->text_domain ), $bulk_counts['locked'], $this->args['labels']['singular_name'], $this->args['labels']['name'] ),
            'deleted'   => sprintf( _n( '%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $bulk_counts['deleted'], app()->text_domain ), $bulk_counts['deleted'], $this->args['labels']['singular_name'], $this->args['labels']['name'] ),
            'trashed'   => sprintf( _n( '%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $bulk_counts['trashed'], app()->text_domain ), $bulk_counts['trashed'], $this->args['labels']['singular_name'], $this->args['labels']['name'] ),
            'untrashed' => sprintf( _n( '%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $bulk_counts['untrashed'], app()->text_domain ), $bulk_counts['untrashed'], $this->args['labels']['singular_name'], $this->args['labels']['name'] ),
        );
        return $bulk_messages;
    }

    protected function create_labels( ): array {

        $d = app()->text_domain;
        
        $tax = $this->args['labels']['name'];
        $tax_single = $this->args['labels']['singular_name'];
        
        return array_merge( array(
            'name'                  => _x( $tax, 'Post type general name', $d ),
            'singular_name'         => _x( $tax_single, 'Post type singular name', $d ),
            'menu_name'             => _x( $tax, 'Admin Menu text', $d ),
            'name_admin_bar'        => _x( $tax_single, 'Add New on Toolbar', $d ),
            'add_new'               => __( 'Add New', $d ),
            'add_new_item'          => __( 'Add New '.$tax_single, $d ),
            'new_item'              => __( 'New '.$tax, $d ),
            'edit_item'             => __( 'Edit '.$tax, $d ),
            'view_item'             => __( 'View '.$tax_single, $d ),
            'all_items'             => __( 'All '.$tax, $d ),
            'search_items'          => __( 'Search '.$tax, $d ),
            'parent_item_colon'     => __( 'Parent '.$tax.':', $d ),
            'not_found'             => __( 'No '.$tax.' found.', $d ),
            'not_found_in_trash'    => __( 'No '.$tax.' found in Trash.', $d ),
            'featured_image'        => _x( $tax_single.' cover image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', $d ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', $d ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', $d ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', $d ),
            'archives'              => _x( $tax_single.' archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', $d ),
            'insert_into_item'      => _x( 'Insert into '.$tax_single, 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', $d ),
            'uploaded_to_this_item' => _x( 'Uploaded to this '.$tax_single, 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', $d ),
            'filter_items_list'     => _x( 'Filter '.$tax_single.' list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', $d ),
            'items_list_navigation' => _x( $tax_single.' list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', $d ),
            'items_list'            => _x( $tax_single.' list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', $d ),
        ),
            $this->args['labels']
        );
    }


    public function __get( string $key ): mixed {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
            return app()->make( LoggerInterface::class )->error( 'The custom post type key ' . $key . ' could not be found.', 'custom_post_type_param_not_set' );
        }
    }


}