<?php

namespace Netdust\Service\Posts;


use Netdust\Traits\Features;
use Netdust\Traits\Setters;

use Netdust\Utils\Logger\Logger;

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
    protected $type = '';

    /**
     * The post type args.
     *
     * @since 1.0.0
     *
     * @var array The list of post type args. See https://developer.wordpress.org/reference/functions/register_post_type/
     */
    protected $args = [];

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
    public function do_actions() {
        add_action( 'init', [ $this, 'register' ] );
        add_filter( 'rest_' . $this->type . '_query', [ $this, 'rest_query' ], 10, 2 );
    }

    /**
     * Updates REST Requests to use prepared query arguments for REST Requests.
     *
     * @since 1.0.0
     *
     * @param array            $args
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    public function rest_query( $args, \WP_REST_Request $request ) {
        return $this->prepare_query_args( $args );
    }

    /**
     * Registers the post type.
     *
     * @since 1.0.0
     */
    public function register() {

        $registered = register_post_type( $this->type, $this->args );

        if ( is_wp_error( $registered ) ) {
            Logger::error( $registered->get_error_message(), $registered->get_error_code(), $registered->get_error_data() );
        } else {
            Logger::info(
                'The custom post type ' . $this->type . ' has been registered.',
                'custom_post_type_registered'
            );
        }
    }

    public function __get( $key ) {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
            return new \WP_Error( 'custom_post_type_param_not_set', 'The custom post type key ' . $key . ' could not be found.' );
        }
    }

    /**
     * Run a WP_Query against this post type.
     *
     * @since 1.0.0
     *
     * @param array $args Query arguments to provide.
     *
     * @return \WP_Query The WP Query object.
     */
    public function query( $args = [] ) {
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
    public function prepare_query_args( array $args ) {
        $args['post_type'] = $this->type;

        return $args;
    }

}