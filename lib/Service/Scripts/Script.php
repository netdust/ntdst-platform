<?php

namespace Netdust\Service\Scripts;


use Netdust\Traits\Features;
use Netdust\Traits\Setters;

use Netdust\Utils\Logger\Logger;
use Netdust\Utils\Logger\LoggerInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Script implements ScriptInterface {

    use Setters;
    use Features;

    /**
     * The handle for this script.
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
     * The source url for this script.
     *
     * @since 1.0.0
     * @var bool|string
     */
    public $src = false;

    /**
     * The dependencies for this script.
     *
     * @since 1.0.0
     * @var array
     */
    public $deps = [];

    /**
     * If this script should be displayed in the footer.
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
     * Params to send to the script when it is enqueued.
     *
     * @since 1.0.0
     *
     * @var array Array of params.
     */
    public $localized_params = [];

    /**
     * The variable name for the localized object.
     * Defaults to the handle if not set.
     *
     * @since 1.0.0
     *
     * @var string The localized object name.
     */
    public $localized_var;

    /**
     * Script constructor
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
     * Returns true if the script has been enqueued. Bypasses doing it wrong check.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_enqueued() {
        return (bool) wp_scripts()->query( $this->handle, 'enqueued' );
    }

    /**
     * Registers this script.
     * In-general, this should automatically run based on the contexts provided in the class.
     *
     * @since 1.0.0
     */
    public function register() {

        $this->get_dependencies();

        $registered = wp_register_script( $this->handle, $this->src, $this->deps, $this->ver, $this->in_footer );

        if ( false === $registered ) {
            Logger::error(
                'The script ' . $this->handle . ' failed to register. That is all I know, unfortunately.',
                'script_was_not_registered',
                [ 'ref' => $this->handle ]
            );
        } else {
            Logger::info(
                'The script ' . $this->handle . ' registered successfully.',
                'script_was_registered'
            );
        }
    }

    /**
     * Enqueues the script, and auto-localizes values if necessary.
     *
     * @since 1.0.0
     */
    public function enqueue(): void {

        $this->localize();

        wp_enqueue_script( $this->handle );

        // Confirm it was enqueued.
        if ( wp_script_is( $this->handle ) ) {
	        app()->make( LoggerInterface::class )->info(
                'The script ' . $this->handle . ' has been enqueued.',
                'script_was_enqueued'
            );

        } else {
	        app()->make( LoggerInterface::class )->error(
                'The script ' . $this->handle . ' failed to enqueue.',
                'script_failed_to_enqueue',
                [ 'ref' => $this->handle, 'src' => $this->src ]
            );

        }

    }
    /**
     * Get the dependencies if the file is found.
     *
     * @since 3.0.0
     *
     */
    protected function get_dependencies( ): void {

        if ( empty( $this->localized_var ) ) {
            $this->localized_var = $this->handle;
        }

        if ( is_string( $this->deps ) ) {
            if ( file_exists( $this->deps ) ) {
                $file       = wp_parse_args( require( $this->deps ), [ 'dependencies' => [], 'version' => '' ] );
                $this->deps = $file['dependencies'];

                // Only set the version if a version has not been specified otherwise.
                if ( false === $this->ver ) {
                    $this->ver = $file['version'];
                }
            } else {
	            app()->make( LoggerInterface::class )->error(
                    'A dependency file was specified, but it could not be found.',
                    'dependencies_file_not_found',
                    [
                        'handle' => $this->handle,
                        'file'   => $this->deps,
                    ]
                );
                $this->deps = [];
            }
        }
    }

    /**
     * Retrieves the specified localized param.
     *
     * @since 1.0.0
     *
     * @param $param
     * @return mixed|\WP_Error
     */
    public function get_param( string $param ): mixed {
        if ( isset( $this->localized_params[ $param ] ) ) {
            return $this->localized_params[ $param ];
        }

        return new \WP_Error( 'param_not_set', 'The localized param ' . $param . ' is not set.' );
    }

    /**
     * Adds a param to localize to this script.
     *
     * @since 1.0.0
     *
     * @param string $key   The key for the localized value.
     * @param mixed  $value The value
     * @return true|\WP_Error True if successful, \WP_Error if param was added too late.
     */
    public function set_param( string $key, mixed $value ): bool|\WP_Error {

        // If the script is already enqueued, return an error.
        if ( $this->is_enqueued() ) {
	        return app()->make( LoggerInterface::class )->error(
		        'The localized param ' . $key . ' was set after the script was already enqueued.',
		        'param_set_too_late'
	        );
        }

        $this->localized_params[ $key ] = $value;

	    return true;
    }

    /**
     * Removes a localized param.
     *
     * @param string $key The key to remove.
          *
     * @return true|\WP_Error True if successful, \WP_Error if param was added too late.
     *@since 1.0.0
     *
     */
    public function remove_param( string $key ): bool|\WP_Error {

        // If the script is already enqueued, return an error.
        if ( wp_script_is( $this->handle ) ) {
            return app()->make( LoggerInterface::class )->error(
                'The localized param ' . $key . ' attempted to be removed after the script was already enqueued.',
                'param_removed_too_late',
                [ 'handle' => $this->handle, 'key' => $key ]
            );
        }

        if ( isset( $this->localized_params[ $key ] ) ) {
            unset( $this->localized_params[ $key ] );
        }

        return true;
    }

    /**
     * Callback to retrieve the localized parameters for this script.
     * If this is empty, localize does not fire.
     *
     * @since 1.0.0
     * @return array list of localized params as key => value pairs.
     */
    public function get_localized_params(): array {
        return $this->localized_params;
    }

    /**
     * Localizes the script, if there are any arguments to pass.
     *
     * @since 1.0.0
     */
    public function localize(): void {
        $localized_params = $this->get_localized_params();

        // If we actually have localized params, localize and enqueue.
        if ( ! empty( $localized_params ) ) {
            $localized = wp_localize_script( $this->handle, $this->localized_var, $localized_params );

            if ( false === $localized ) {
	            app()->make( LoggerInterface::class )->error(
                    'The script ' . $this->handle . ' failed to localize. That is all I know, unfortunately.',
                    'script_was_not_localized',
                    [ 'handle' => $this->handle, 'params' => $localized_params ]
                );
            } else {
	            app()->make( LoggerInterface::class )->info(
                    'The script ' . $this->handle . ' localized successfully.',
                    'script_was_localized'
                );
            }
        }
    }

    public function __get( string $key ): mixed {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
	        return app()->make( LoggerInterface::class )->error(
		        'The key ' . $key . ' could not be found.',
		        'scrip_param_not_set'
	        );
        }
    }

}