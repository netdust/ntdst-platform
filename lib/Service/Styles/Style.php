<?php

namespace Netdust\Service\Styles;


use Netdust\Service\Logger\LoggerInterface;
use Netdust\Traits\Features;
use Netdust\Traits\Setters;

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
     */
    public string $handle;

    /**
     * The version.
     *
     * @since 1.0.0
     */
    public string $ver = '0.1';

    /**
     * The source url for this style.
     *
     * @since 1.0.0
     * @var bool|string
     */
    public string|bool $src = false;

    /**
     * The dependencies for this style.
     *
     * @since 1.0.0
     * @var array
     */
    public array $deps = [];

    /**
     * If this style should be displayed in the footer.
     *
     * @since 1.0.0
     * @var bool
     */
    public bool $in_footer = false;

    /**
     * If this script needs decoration.
     *
     * @since 1.0.0
     * @var array
     */
    public array $middlewares = [];

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
    public function do_actions(): void {
        add_action( 'init', [ $this, 'register' ] );
    }

    /**
     * Returns true if the style has been enqueued. Bypasses doing it wrong check.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_enqueued(): bool {
        return (bool) wp_styles()->query( $this->handle, 'enqueued' );
    }

    /**
     * Returns true if the style has been registered. Bypasses doing it wrong check.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_registered(): bool {
        return (bool) wp_styles()->query( $this->handle, 'registered' );
    }

    /**
     * Registers this style.
     * In-general, this should automatically run based on the contexts provided in the class.
     *
     * @since 1.0.0
     */
    public function register(): void {

        $registered = wp_register_style( $this->handle, $this->src, $this->deps, $this->ver, $this->in_footer );


        if ( false === $registered ) {
            app()->make( LoggerInterface::class )->error(
                'The style ' . $this->handle . ' failed to register. That is all I know, unfortunately.',
                'style_was_not_registered',
                [ 'ref' => $this->handle ]
            );
        } else {
	        app()->make( LoggerInterface::class )->info(
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
    public function enqueue(): void {

        wp_enqueue_style( $this->handle );

        // Confirm it was enqueued.
        if ( wp_style_is( $this->handle ) ) {
	        app()->make( LoggerInterface::class )->info(
                'The style ' . $this->handle . ' has been enqueued.',
                'style_was_enqueued'
            );
        } else {
	        app()->make( LoggerInterface::class )->error(
                'The style ' . $this->handle . ' failed to enqueue.',
                'style_failed_to_enqueue',
                [ 'ref' => $this->handle ]
            );
        }

    }

    public function __get( string $key ): mixed {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
	        return app()->make( LoggerInterface::class )->error(
		        'The key ' . $key . ' could not be found',
		        'style_param_not_set'
	        );
        }
    }

}