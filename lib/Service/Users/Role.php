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

    /**
     * id
     * @var string
     */
    protected string $id = '';

    /**
     * name
     * @var string
     */
    protected string $name = '';

    /**
     * capabilities
     * @var array
     */
    protected array $capabilities = array();

    /**
     * Role constructor
     * @param array $capabilities Overrides to default args in the object
     */
    public function __construct( string $role, string $display_name, array $capabilities = [] ) {
        $this->id = $role;
        $this->name = $display_name;
        $this->capabilities = $capabilities;
    }

    public function do_actions(): void {
        add_action( 'init', [ $this, 'register' ] );
    }

    public function register(): ?\WP_Role {
        global $wp_roles;
        if (!isset($wp_roles))
            $wp_roles = new \WP_Roles();

        return add_role($this->id, $this->name, $this->capabilities);
    }

    public function update(): ?\WP_Role
    {
        remove_role($this->id);
        return $this->register();
    }

}