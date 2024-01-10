<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Netdust\Service\Pages\Admin;

use Netdust\App;
use Netdust\Traits\Setters;
use Netdust\Traits\Templates;

use Netdust\Utils\Logger\Logger;

use Netdust\Utils\UI\SettingsField;
use Netdust\Utils\UI\UIInterface;
use WP_Error;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminSection
 *
 * @since 1.0.0
 *
 * @since
 * @package
 */
class AdminSection {

	use Templates;
    use Setters;

	/**
	 * The section ID
	 *
	 * @var string
	 */
	protected $id = '';

    /**
     * The parent section ID
     *
     * @var string
     */
    protected $parent_id = '';

    public function id():string {
        return $this->id;
    }

    /**
     * The section name
     *
     * @var string
     */
	public $name = '';

	/**
	 * List of fields that were successfully saved in this request.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $saved_fields = [];

	/**
	 * The nonce action used to validate when interfacing with this page.
	 *
	 * @since 1.0.0
	 *
	 * @var string the nonce action.
	 */
	protected $nonce_action;

	/**
	 * The key to use when updating options.
	 *
	 * @since 1.0.0
	 *
	 * @var string The options key
	 */
	protected $options_key = false;

    /**
     * Section views
     * Created views() method.
     *
     * @since 1.0.0
     *
     * @var
     */
    protected $views = [];

	/**
	 * Section fields
	 * Created using fields() method.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	protected $fields = [];

	/**
	 * Determines if this request is valid for saving.
	 * Generally, this is passed down from the admin page.
	 *
	 * @var
	 */
	private $valid_request;

	/**
	 * Admin_Section constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of arguments used to create this menu page.
	 */
	public function __construct(  array $args = [] ) {
        $this->set_values( $args );
        if( !empty($this->views) ) foreach ($this->views as &$view ) {
            App::get(AdminSection::class)->add(
                $view['id'],
                array_merge( $view, ['singleton'=>true, 'parent_id'=>$this->id] )
            );
            $view = App::get( $view['id'] );
        }
        if( !empty($this->fields) ) foreach ($this->fields as &$field ) {
            app(UIInterface::class)->make( $field['type'], $field['value'],  $field, false);
        }
		$this->options_key = false === $this->options_key ? $this->id . '_settings' : $this->options_key;
	}

    public function get_url( $query = [], $page='' ) {

        if( empty( $this->parent_id ) ){
            $url = add_query_arg( array(
                'page' => app()->config('admin')['menu_slug'],
                'section' => $this->id,
            ), get_admin_url( null, 'admin.php' ) );
        }
        else {
            $url = add_query_arg( array(
                'view' => $this->id
            ), App::container()->get( $this->parent_id )->get_url( ) );
        }

        return add_query_arg( $query, $url );
    }

    public function get_view_url( $view, $query = [] ) {

        $url = $this->get_url( $query );

        if ( ! is_wp_error( $this->view( $view ) ) ) {
            $url = add_query_arg( 'view', $view, $url );
        }

        return apply_filters( 'admin_section:view_url', $url );
    }

    public function get_current_view_key() {
        if ( isset( $_GET['view'] ) && ! is_wp_error( $this->view( $_GET['view'] ) ) ) {
            return $_GET['view'];
        }

        if( is_array( $this->views ) && count( $this->views ) > 0 ) {
            return reset($this->views )->id();
        }

        return null;
    }

    public function view( $id = '' ) {

        if ( '' === $id ) {
            $id = $this->get_current_view_key();
        }

        if ( isset( $this->views[ $id ] ) && $this->views[ $id ] instanceof AdminSection ) {
            return $this->views[ $id ];
        }

        return Logger::error(
            'No valid view could be found',
            'no_section_view_found',
            [ 'views' => $this->views, 'id'=> $id, 'section' => $this->id ]
        );

    }

    public function get_views() {
        return apply_filters('admin_section:views', $this->views );
    }

    public function setParent( $id ) {
        $this->parent_id = $id;
    }

	public function get_field( $key ) {
		if ( isset( $this->fields[ $key ] ) ) {
			if ( ! $this->fields[ $key ] instanceof SettingsField ) {
				$this->fields[ $key ] = app(UIInterface::class)->make( $this->fields[ $key ]['type'], $this->fields[ $key ]['value'],  $this->fields[ $key ], false);
			}

			return $this->fields[ $key ];
		}

		return Logger::error(
            'The provided field could not be found',
			'invalid_field',
			[ 'key' => $key ]
		);
	}

	/**
	 * Updates a settings field.
	 *
	 * @since 1.1.0
	 *
	 * @param SettingsField $field
	 *
	 * @return mixed|WP_Error
	 */
	public function update_field( SettingsField $field ) {
		// Get the field name.
		$field_name = $field->get_field_param( 'name' );

		// If the field type is a checkbox, update value based on if the field was included.
		if ( 'checkbox' === $field->get_field_type() ) {
			$checked = isset( $_POST[ $field_name ] );
			$updated = $field->update_value( $checked );

			// Otherwise, Update the value if the field is provided.
		} elseif ( isset( $_POST[ $field_name ] ) && $field->sanitize( $_POST[ $field_name ] ) !== $field->value ) {
			$updated = $field->update_value( $_POST[ $field_name ] );
		}

		if ( ! isset( $updated ) ) {
            return new \WP_Error( 'field_not_changed', 'The field was not updated because the value is the same as the current field value', [
                'field_name' => $field_name,
                'value'      => $_POST[ $field_name ] ?? 'value not set',
            ]);
		}

		return $updated;
	}

	/**
	 * Saves a single field to the database.
	 *
	 * @since 1.0.0
	 *
	 * @param SettingsField $field The field to save.
	 *
	 * @return true|WP_Error true if the field saved, WP_Error otherwise.
	 */
	public function save_field( SettingsField $field ) {
		$options_key = $this->options_key;
		$updated     = $this->update_field( $field );

		// Bail early if this field was already set.
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

        update_option( $field->get_setting_key(), $updated );

		return $updated;
	}


	/**
	 * Action to save all fields.
	 *
	 * @since 1.0.0
	 *
	 */
	public function save(): bool|WP_Error {
		$errors = new \WP_Error;

		foreach ( $this->fields as $key => $field ) {
			$field = $this->get_field( $key );
            $errors = $this->save_field( $field );

			if ( !is_wp_error( $errors ) ) {
                $this->saved_fields[ $field->get_field_param( 'name' ) ] = $field;
			}
		}

		return is_wp_error( $errors ) ? $errors : true;
	}


	/**
	 * Fetches the template group name.
	 *
	 * @since 1.0.0
	 *
	 * @return string The template group name
	 */
	protected function get_template_group() {
		return 'admin/sections/'.  (isset( $this->parent_id ) ? $this->parent_id.'/' : '') . $this->id;
	}

    public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_Error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}
}