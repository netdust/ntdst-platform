<?php
/**
 * Taxonomy Abstraction.
 *
 * @since   1.0.0
 */


namespace Netdust\Service\Posts;

use Netdust\Logger\Logger;
use Netdust\Logger\LoggerInterface;
use Netdust\Traits\Features;
use Netdust\Traits\Setters;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Taxonomy
 *
 * @since   1.0.0
 */
class Taxonomy {

    /**
     * Taxonomy ID (slug).
     *
     * @since 1.2.0
     *
     * @var string The Taxonomy identifier
     */
    protected string $id = '';

    /**
     * The post type args.
     *
     * @since 1.0.0
     *
     * @var array The list of post type args. See https://developer.wordpress.org/reference/functions/register_post_type/
     */
    protected array $args = [];

    /**
     * The post type, or types to use.
     *
     * @var string|array A single post type or array of post types
     */
    protected string|array $post_type = 'post';

    /**
     * Taxonomy constructor.
     *
     * @param array $args Overrides to default args in the object
     */
    public function __construct( string $taxonomy, array|string $object_type = [], array $args = [] ) {
        $this->id = $taxonomy;
        $this->post_type = $object_type;
        $this->args =  $args;
    }

    /**
     * @inheritDoc
     */
    public function do_actions(): void {
        add_action( 'init', [ $this, 'register' ], 11 );
    }

    /**
     * Registers the post type.
     *
     * @since 1.0.0
     */
    public function register():  bool|\WP_Error {

        // Fallback to name.
        if ( ! isset( $this->args['label'] ) ) {
            $this->args['label'] =  $this->args['name'];
        }

        $this->args['labels'] = $this->create_labels( );

        if( ! isset( $this->args['rest_base'] ) ) {
            $this->args['rest_base'] = $this->id . 's';
        }

        $registered = register_taxonomy( $this->id, $this->post_type, $this->args );

        foreach( (array) $this->post_type as $object_type ) {
            register_taxonomy_for_object_type( $this->id , $object_type);
        }

        if ( is_wp_error( $registered ) ) {
	        return app()->make( LoggerInterface::class )->error( $registered->get_error_message(), $registered->get_error_code(), $registered->get_error_data() );
        } else {
	        app()->make( LoggerInterface::class )->info(
                'The taxonomy ' . $this->id . ' has been registered to '.  print_r($this->post_type, true ) . '.',
                'registered_taxonomy'
            );
        }

		return true;
    }

    public function __get( string $key ): mixed {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
            return app()->make( LoggerInterface::class )->error( 'The key ' . $key . ' could not be found.', 'param_not_set' );
        }
    }

    /**
     * Retrieves the terms for this taxonomy.
     *
     * @since 1.0.0
     *
     * @param array $args Taxonomy args. See WP_Term_Query for a list of args.
     *
     * @return \WP_Term_Query The query results.
     */
    public function query( array $args ): \WP_Term_Query {
        $args['taxonomy'] = $this->id;
        return new \WP_Term_Query( $args );
    }

    /**
     * Deletes a single term.
     *
     * @since 1.0.0
     *
     * @param int          $term          The term ID.
     * @param array|string $args {
     *                                        Optional. Array of arguments to override the default term ID. Default empty
     *                                        array.
     *
     *     @type int  $default       The term ID to make the default term. This will only override
     *                               the terms found if there is only one term found. Any other and
     *                               the found terms are used.
     *     @type bool $force_default Optional. Whether to force the supplied term as default to be
     *                               assigned even if the object was not going to be term-less.
     *                               Default false.
     *     }
     *
     * @return bool|int|\WP_Error True on success, false if term does not exist. Zero on attempted
     *                            deletion of default Category. WP_Error if the taxonomy does not exist.
     */
    protected function _delete( int $term, array|string $args = [] ):  mixed {
        return wp_delete_term( $term, $this->id, $args );
    }

    /**
     * Update a taxonomy term with new data.
     *
     * @param int          $term_id The ID of the term.
     * @param array $args    Term update args. See wp_update_term
     *
     * @return  mixed An array containing the `term_id` and `term_taxonomy_id`,
     *                         WP_Error otherwise.
     *@since 1.0.0
     *
     */
    protected function _update( int $term_id, array $args ):  mixed {
        return wp_update_term( $term_id, $this->id, $args );
    }

    /**
     * Insert a term.
     *
     * @since 1.0.0
     *
     * @param int $term     The term name to add.
     * @param array|string  $args     Term insert args. see wp_insert_term.
     *
     * @return mixed An array containing the `term_id` and `term_taxonomy_id`,
     *                         WP_Error otherwise.
     */
    protected function _insert( int $term, array|string $args ): mixed {
        return wp_insert_term( $term, $this->id, $args );
    }

    /**
     * Saves a term to the database.
     *
     * @since 1.0.0
     *
     * @param array $args
     *
     * @return array|\WP_Error An array containing the `term_id` and `term_taxonomy_id`,
     *                         WP_Error otherwise.
     */
    public function save( array $args ): \WP_Error|array {

        if ( isset( $args['id'] ) ) {
            $id = $args['id'];
            unset( $args['id'] );
        } elseif ( isset( $args['name'] ) ) {
            $name = $args['name'];
            unset( $args['name'] );
        }

        if ( empty( $name ) ) {
            return app()->make( LoggerInterface::class )->error(
                'To save a term, you must provide an id or a term name to create.',
                'save_term_invalid_args',
                [ 'args' => $args ]
            );
        }

        $saved = isset( $id ) ? $this->_update( $id, $args ) : $this->_insert( $name, $args );

        if ( is_wp_error( $saved ) ) {
	        return app()->make( LoggerInterface::class )->error( $saved->get_error_message(), $saved->get_error_code(), $saved->get_error_data() );
        } else {
	        app()->make( LoggerInterface::class )->debug(
                'A ' . $this->id . ' term was saved',
                $this->id . '_saved'
            );
        }

        return $saved;
    }

    /**
     * Deletes a single term.
     *
     * @since 1.0.0
     *
     * @param int          $term          The term ID.
     * @param bool         $force_delete  Optional. Whether to bypass Trash and force deletion.
     *                                    Default false.
     * @param array|string $args {
     *                                        Optional. Array of arguments to override the default term ID. Default empty
     *                                        array.
     *
     *     @type int  $default       The term ID to make the default term. This will only override
     *                               the terms found if there is only one term found. Any other and
     *                               the found terms are used.
     *     @type bool $force_default Optional. Whether to force the supplied term as default to be
     *                               assigned even if the object was not going to be term-less.
     *                               Default false.
     *     }
     *
     * @return mixed True on success, false if term does not exist. Zero on attempted
     *                            deletion of default Category. WP_Error if the taxonomy does not exist.
     */
    public function delete( int $term, array $args = [] ): mixed {
        $deleted = $this->_delete( $term, $args );

        if ( false === $deleted ) {
            return app()->make( LoggerInterface::class )->warning(
	            'The provided term could not be deleted because it does not exist',
                'term_does_not_exist',
                [ 'args' => $args, 'term' => $term ]
            );
        } elseif ( is_wp_error( $deleted ) ) {
	        return app()->make( LoggerInterface::class )->error( $deleted->get_error_message(), $deleted->get_error_code(), $deleted->get_error_data()  );
        }

        return $deleted;
    }

    protected function create_labels( ): array {

        $d = app()->text_domain;
        
        $tax = $this->args['labels']['name'] ?? $this->args['label'];
        $tax_single = $this->args['labels']['singular_name'] ?? $this->args['label'];

        return array_merge( array(
            'name'                  => _x( $tax, 'Post type general name', $d ),
            'singular_name'         => _x( $tax_single, 'Post type singular name', $d ),
            'search_items'          => __( 'Search '.$tax, $d ),
            'all_items'             => __( 'All '.$tax, $d ),

            'edit_item'             => __( 'Edit '.$tax_single, $d ),
            'view_item'             => __( 'View '.$tax_single, $d ),
            'update_item'           => __( 'Update '.$tax_single, $d ),
            'add_new_item'          => __( 'Add New '.$tax_single, $d ),
            'new_item_name'         => __( 'New '.$tax_single.' Name', $d ),


            // hierarchical
            'parent_item'           => __( 'Parent '.$tax, $d ),
            'parent_item_colon'     => __( 'Parent '.$tax.':', $d ),


            'not_found'             => __( 'No '.$tax.' found.', $d ),
            'not_found_in_trash'    => __( 'No '.$tax.' found in Trash.', $d ),
            'no_terms '             => __( 'No '.$tax, $d ),

            'items_list_navigation' => _x( $tax_single.' list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', $d ),
            'items_list'            => _x( $tax_single.' list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', $d ),
        ),
            $this->args['labels']??[]
        );
    }

}