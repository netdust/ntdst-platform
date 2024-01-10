<?php

namespace Netdust\Service\Posts;


use Netdust\Traits\Features;
use Netdust\Traits\Setters;

use Netdust\Utils\Logger\Logger;
use Netdust\Utils\Logger\LoggerInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Post {

    use Setters;
    use Features;

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
    public function __construct( array $args = [] ) {
        $this->set_values( $args );
    }

    /**
     * @inheritDoc
     */
    public function do_actions(): void {
        add_action( 'init', [ $this, 'register' ] );
        add_filter( 'rest_' . $this->type . '_query', [ $this, 'rest_query' ], 10, 2 );
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

        add_filter( 'enter_title_here', [$this,'change_title_text'] );

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

    public function change_title_text( string $title ): string {
        $screen = get_current_screen();

        if  ( $this->type == $screen->post_type ) {
            $title = $this->args['labels']['singular_name'].' title';
        }
        return $title;
    }

    public function __get( string $key ): mixed {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
            return app()->make( LoggerInterface::class )->error( 'The custom post type key ' . $key . ' could not be found.', 'custom_post_type_param_not_set' );
        }
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
        $args['post_type'] = $this->type;

        return $args;
    }

    protected function create_labels( ): array {

        $tax = $this->args['labels']['name'];
        $tax_single = $this->args['labels']['singular_name'];

        return array_merge( array(
            'name'                  => _x( $tax, 'Post type general name', app()->text_domain ),
            'singular_name'         => _x( $tax_single, 'Post type singular name', app()->text_domain ),
            'menu_name'             => _x( $tax, 'Admin Menu text', app()->text_domain ),
            'name_admin_bar'        => _x( $tax_single, 'Add New on Toolbar', app()->text_domain ),
            'add_new'               => __( 'Add New', app()->text_domain ),
            'add_new_item'          => __( 'Add New '.$tax_single, app()->text_domain ),
            'new_item'              => __( 'New '.$tax, app()->text_domain ),
            'edit_item'             => __( 'Edit '.$tax, app()->text_domain ),
            'view_item'             => __( 'View '.$tax_single, app()->text_domain ),
            'all_items'             => __( 'All '.$tax, app()->text_domain ),
            'search_items'          => __( 'Search '.$tax, app()->text_domain ),
            'parent_item_colon'     => __( 'Parent '.$tax.':', app()->text_domain ),
            'not_found'             => __( 'No '.$tax.' found.', app()->text_domain ),
            'not_found_in_trash'    => __( 'No '.$tax.' found in Trash.', app()->text_domain ),
            'featured_image'        => _x( $tax_single.' cover image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', app()->text_domain ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', app()->text_domain ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', app()->text_domain ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', app()->text_domain ),
            'archives'              => _x( $tax_single.' archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', app()->text_domain ),
            'insert_into_item'      => _x( 'Insert into '.$tax_single, 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', app()->text_domain ),
            'uploaded_to_this_item' => _x( 'Uploaded to this '.$tax_single, 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', app()->text_domain ),
            'filter_items_list'     => _x( 'Filter '.$tax_single.' list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', app()->text_domain ),
            'items_list_navigation' => _x( $tax_single.' list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', app()->text_domain ),
            'items_list'            => _x( $tax_single.' list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', app()->text_domain ),
        ),
            $this->args['labels']
        );
    }

}