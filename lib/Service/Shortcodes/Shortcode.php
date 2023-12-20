<?php
/**
 * Registers a shortcode
 *
 * @since   1.0.0
 */


namespace Netdust\Service\Shortcodes;

use Netdust\Utils\Logger\Logger;

use Netdust\Traits\Templates;
use Netdust\Traits\Features;
use Netdust\Utils\Logger\LoggerInterface;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Shortcode
 *
 * @since   1.0.0
 */
abstract class Shortcode {

    use Templates;
    use Features;

    /**
     * The shortcode attributes, parsed by shortcode atts.
     *
     * @since 1.0.0
     *
     * @var array
     */
    protected array $atts = [];

    /**
     * The default shortcode att values.
     *
     * @since 1.0.0
     *
     * @var array
     */
    protected array $defaults = [];

    /**
     * The name of this shortcode.
     *
     * @since 1.0.0
     *
     * @var string
     */
    protected string $shortcode;

    /**
     * Shortcode constructor
     *
     * @param array $args Overrides to default args in the object
     */
    public function __construct( string $shortcode, array $args = [] ) {
        $this->shortcode = $shortcode;
        $this->defaults = array_merge( $this->defaults, $args );
    }

    /**
     * The actions this shortcode should take when called.
     *
     * @since 1.0.0
     * @param array $atts Parsed shortcode attributes.
     *
     * @return mixed The shortcode action result.
     */
    protected abstract function shortcode_actions( array $atts ): string;

    /**
     * @inheritDoc
     */
    public function do_actions(): void {
        add_shortcode( $this->shortcode, [ $this, 'shortcode' ] );

	    app()->make( LoggerInterface::class )->info(
            'A shortcode has been added',
            'shortcode'
        );
    }

    /**
     * The actual shortcode callback. Sets shortcode atts to the class so other methods can access the arguments.
     *
     * @since 1.0.0
     *
     * @param array $atts The shortcode attributes
     * @return mixed The shortcode action result.
     */
    public function shortcode( array $atts = [], string $content='', string $shortcode='' ): string {
        $atts = array_merge( $this->defaults, is_array($atts) ? $atts : [] );
        return $this->shortcode_actions( $atts );
    }

    protected function get_template_group(): string {
        return 'shortcodes';
    }


    public function __get( string $key ): mixed {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
	        return app()->make( LoggerInterface::class )->error(
		        'The key ' . $key . ' could not be found.',
		        'shortcode_param_not_set'
	        );
        }
    }

}