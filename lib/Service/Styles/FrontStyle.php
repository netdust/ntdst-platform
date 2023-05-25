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
    public function __construct(StyleInterface $style ) {
        $this->decorated = $style;
    }

    public function do_actions() {
        add_action( 'wp_enqueue_styles', [ $this, 'enqueue' ] );
        $this->decorated->do_actions();
    }

    public function enqueue() {
        if( !is_admin() ) {
            $this->decorated->enqueue();
        }
    }
}