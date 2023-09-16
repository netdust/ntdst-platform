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
            App::container()->get(AdminSection::class)->add( $view['id'], $view );
            $view = App::container()->get( $view['id'] );
            $view->setParent( $this->id );

        }
		$this->options_key = false === $this->options_key ? $this->id . '_settings' : $this->options_key;
	}

    public function get_url( $query = [] ) {

        $url = add_query_arg( array(
            'page' => $_REQUEST['page'],
            'section' => $this->id,
        ), get_admin_url( null, 'admin.php' ) );

        return add_query_arg( $query, $url );
    }

    public function get_view_url( $view ) {

        $url = $this->get_url();

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
				$this->fields[ $key ] = self::make_class( $this->fields[ $key ] );
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
			return Logger::error(
				'The field was not updated because the value is the same as the current field value',
                'field_not_changed',
				[
					'field_name' => $field_name,
					'value'      => $_POST[ $field_name ] ?? 'value not set',
				]
			);
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
			return Logger::error(  $updated->get_error_message(), $updated->get_error_code(), $updated->get_error_data()  );
		}

        /*
		// Update the option.
		$updated = Underpin()->options()->get( $options_key )->update( $updated, $field->get_setting_key() );

		if ( true !== $updated ) {
			$updated = Logger::error(
                'A setting failed to update.',
				'update_request_settings_failed_to_update',
				[ 'setting' => $options_key, 'updated_return' => $updated ]
			);
		} else {
			Logger::debug(
                'A setting updated successfully.',
				'update_request_settings_succeeded_to_update',
			);
		}*/

		return $updated;
	}

	/**
	 * Validates this request.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if request is validated, otherwise WP_Error containing what went wrong.
	 */
	public function validate_request() {
		$errors = new \WP_Error();

		foreach ( $this->fields as $key => $field ) {
			if ( ! $this->get_field( $key ) instanceof SettingsField ) {
				$errors->add(
					'field_invalid',
					'The provided field is not an instance of a settings field',
					[ 'field' => $field ]
				);
			}
		}

		return $errors->has_errors() ? $errors : true;
	}

	/**
	 * Action to save all fields.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if all fields were saved, WP_Error containing errors if not.
	 */
	public function save() {
		$errors = new \WP_Error;

		foreach ( $this->fields as $key => $field ) {
			$field = $this->get_field( $key );
			$saved = $this->save_field( $field );

			if ( is_wp_error( $saved ) || ! $field instanceof SettingsField ) {
				if ( 'field_not_changed' !== $saved->get_error_code() ) {
					Logger::debug( $saved->get_error_code(), 'failed_to_save_field');
				}
			} else {
				$this->saved_fields[ $field->get_field_param( 'name' ) ] = $field;
			}
		}

		if ( $errors->has_errors() ) {
			Logger::debug(
                'some settings failed to save',
				'failed_to_save_settings'
			);
		}

		return $errors->has_errors() ? true : $errors;
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