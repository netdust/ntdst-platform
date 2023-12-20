<?php

namespace Netdust\Service\Styles;

use Netdust\Traits\Decorator;
use Netdust\Traits\Features;
use Netdust\Traits\Setters;

class FrontStyle implements StyleInterface
{
    use Setters;
    use Features;
    use Decorator;

	public $decorated;

    public function __construct( StyleInterface $style ) {
        $this->decorated = $style;
    }

    public function do_actions(): void {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
        $this->decorated->do_actions();
    }

    public function enqueue(): void {
        if( !is_admin() ) {
            $this->decorated->enqueue();
        }
    }
}