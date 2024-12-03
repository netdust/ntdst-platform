<?php
/**
 * Registers a shortcode
 *
 * @since   1.0.0
 */


namespace Netdust\Service\Shortcodes;

use Netdust\App;



if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Shortcode
 *
 * @since   1.0.0
 */
class Shortcode {

    /**
     * The shortcode attributes, parsed by shortcode atts.
     * @var array
     */
    protected array $atts = [];

    /**
     * The shortcode attributes, parsed by shortcode atts.
     * @var callable
     */
    protected $callback;

    /**
     * The default shortcode att values.
     * @var array
     */
    protected array $defaults = [];

    /**
     * The name of this shortcode.
     * @var string
     */
    protected string $tag;

    /**
     * Shortcode constructor
     *
     * @param array $args Overrides to default args in the object
     */
    public function __construct( string $tag, array $args = [] ) {
        $this->tag = $tag;
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
    protected function shortcode_actions( array $atts ): string {
        return APP::template()->render( 'shortcodes/' . $this->tag, $atts );
    }

    /**
     * @inheritDoc
     */
    public function do_actions(): void {
        add_action( 'init', [ $this, 'register' ] );
    }

    public function register(): void {
        add_shortcode( $this->tag, [ $this, 'shortcode' ] );
    }

    /**
     * The actual shortcode callback. Sets shortcode atts to the class so other methods can access the arguments.
     *
     * @since 1.0.0
     *
     * @param array $atts The shortcode attributes
     * @return mixed The shortcode action result.
     */
    public function shortcode( string|array $atts = [], string $content='', string $shortcode='' ): string {
        $atts = array_merge( $this->defaults, is_array($atts) ? $atts : [] );
        return $this->shortcode_actions( $atts );
    }

}