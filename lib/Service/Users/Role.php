<?php
/**
 * Role Abstraction
 *
 * @since   1.1.1
 * @package Underpin\Abstracts
 */

namespace Netdust\Service\Users;

use Netdust\Traits\Features;
use Netdust\Traits\Setters;
use WP_Role;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Role {

    use Features;
    use Setters;

    /**
     * id
     * @var string
     */
    protected string $id = '';

    /**
     * capabilities
     * @var array
     */
    protected array $capabilities = array();

    /**
     * Role constructor
     * @param array $args Overrides to default args in the object
     */
    public function __construct( array $args = [] ) {
        $this->set_values( $args );
    }

    public function do_actions(): void {
        add_action( 'init', [ $this, 'register' ] );
    }

    public function register(): ?\WP_Role {
        return add_role($this->id, $this->name, $this->capabilities);
    }

    public function update(): ?\WP_Role
    {
        remove_role($this->id);
        return $this->register();
    }

}