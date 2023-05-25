<?php

namespace Netdust\Service\Styles;


use Netdust\Traits\Features;
use Netdust\Traits\Setters;
use Netdust\Utils\Logger\Logger;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Style implements StyleInterface {

    use Setters;
    use Features;

    /**
     * The handle for this style.
     *
     * @since 1.0.0
     * @var string the script handle.
     */
    public $handle;

    /**
     * The version.
     *
     * @since 1.0.0
     * @var string
     */
    public $ver = false;

    /**
     * The source url for this style.
     *
     * @since 1.0.0
     * @var bool|string
     */
    public $src = false;

    /**
     * The dependencies for this style.
     *
     * @since 1.0.0
     * @var array
     */
    public $deps = [];

    /**
     * If this style should be displayed in the footer.
     *
     * @since 1.0.0
     * @var bool
     */
    public $in_footer = false;

    /**
     * If this script needs decoration.
     *
     * @since 1.0.0
     * @var bool
     */
    public $middlewares;

    /**
     * Style constructor
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
    }

    /**
     * Returns true if the style has been enqueued. Bypasses doing it wrong check.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_enqueued() {
        return (bool) wp_styles()->query( $this->handle, 'enqueued' );
    }

    /**
     * Registers this style.
     * In-general, this should automatically run based on the contexts provided in the class.
     *
     * @since 1.0.0
     */
    public function register() {

        $registered = wp_register_style( $this->handle, $this->src, $this->deps, $this->ver, $this->in_footer );

        if ( false === $registered ) {
            Logger::error(
                'The style ' . $this->handle . ' failed to register. That is all I know, unfortunately.',
                'style_was_not_registered',
                [ 'ref' => $this->handle ]
            );
        } else {
            Logger::info(
                'The style ' . $this->handle . ' registered successfully.',
                'style_was_registered'
            );
        }
    }

    /**
     * Enqueues the style.
     *
     * @since 1.0.0
     */
    public function enqueue() {

        wp_enqueue_style( $this->handle );

        // Confirm it was enqueued.
        if ( wp_style_is( $this->handle ) ) {
            Logger::info(
                'The style ' . $this->handle . ' has been enqueued.',
                'style_was_enqueued'
            );
        } else {
            Logger::error(
                'The style ' . $this->handle . ' failed to enqueue.',
                'style_failed_to_enqueue',
                [ 'ref' => $this->handle ]
            );
        }

    }

    public function __get( $key ) {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
            return new \WP_Error( 'style_param_not_set', 'The key ' . $key . ' could not be found.' );
        }
    }

}