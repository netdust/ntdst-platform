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

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Role {
    use Features;
    use Setters;

    /**
     * id
     * String that identifies this role.
     *
     * @var string Role
     */
    protected $id = '';

    /**
     * capabilities
     * List of capabilities keyed by the capability name, e.g. array( 'edit_posts' => true, 'delete_posts' => false ).
     *
     * @var array
     */
    protected $capabilities = array();

    /**
     * Role constructor
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
        // Add the role.
        add_role( $this->id, $this->name, $this->capabilities );
    }
}