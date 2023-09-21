<?php
/**
 * Template Loader Trait
 * Handles template loading and template inheritance.
 *
 */

namespace Netdust\Traits;

use Netdust\Utils\Logger\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait Templates
 *
 * @since   1.0.0
 * @package underpin\traits
 */
trait Templates {

	/**
	 * Params
	 *
	 * @since 1.0.0
	 *
	 * @var array of parameter value arrays keyed by their parameter names.
	 */
	private $params = [];

	/**
	 * Depth
	 *
	 * @since 1.0.0
	 *
	 * @var int The current depth of this instance
	 */
	private $depth = 0;

    /**
     * Root path
     *
     * @since 3.0.0
     *
     * @var string The root path to the templates
     */
    protected $template_root;

	/**
	 * Fetches the template group name. This determines the sub-directory for the templates.
	 *
	 * @since 1.0.0
	 *
	 * @return string The template group name
	 */
	protected abstract function get_template_group();


	/**
	 * Gets the specified template, if it is valid.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string The template name to get.
	 * @param $params        array of param values that can be used in the template via get_param().
	 *
	 * @return string The template contents.
	 */
	public function get_template( $template_name, array $params = [] ) {

        if ( $this->template_file_exists( $template_name ) ) {
            $template = $this->include_template( $template_name, $params );
        } else {
            $template_path = $this->get_template_path( $template_name );

            Logger::error(
                "Template $template_name was not loaded because the file located at $template_path does not exist.",
                'template_file_does_not_exist'
            );

            $template = '';

            /**
             * Fires just after the template loader determines that the template file does not exist.
             */
            do_action( 'template:file_not_found', [ 'template_name' => $template_name, 'params' => $params ] );
        }

		return $template;
	}

	/**
	 * Gets the current template depth.
	 *
	 * The template depth goes up each time a template is loaded within the base template. This is used internally to
	 * determine which params should be loaded-in, but it can also be useful when recursively loading in a template.
	 *
	 * @since 1.0.0
	 *
	 * @return int The current template depth.
	 */
	public function get_depth() {
		return $this->depth;
	}

	/**
	 * Get the value of the specified param, if it exists.
	 *
	 * Params are passed into a template via the params argument of get_template.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $param   The param to load.
	 * @param mixed $default (optional) The default value of the param, if it does not exist.
	 *
	 * @return mixed The parameter value, if it exists. Otherwise, this will use the default value.
	 */
	public function get_param( $param, $default = false ) {
		if ( isset( $this->params[ $this->depth ] ) && isset( $this->params[ $this->depth ][ $param ] ) ) {
			$param = $this->params[ $this->depth ][ $param ];
		} else {
			$param = $default;
		}

		return $param;
	}

	/**
	 * Retrieves all the params for the current template.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of params for the current template
	 */
	public function get_params() {
		if ( isset( $this->params[ $this->depth ] ) ) {
			return $this->params[ $this->depth ];
		}

		return [];
	}

    /**
     * Retrieves the template group's path. This determines where templates will be searched for within this plugin.
     *
     * @todo figure out how to set this dynamicly without dependencies
     * @since 1.0.0
     *
     * @return string The full path to the template root directory.
     */
    protected function get_template_root_directory() {
        return $this->template_root ?? dirname( APP_PLUGIN_FILE ) . '/app/templates';
    }

	/**
	 * Gets the template directory based on the template group.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_template_directory() {
		$template_group     = $this->get_template_group();
		$template_directory = trailingslashit( $this->get_template_root_directory() ) . $template_group;

		return $template_directory;
	}

	/**
	 * Gets the template path, given the file name.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string the template name to include.
	 *
	 * @return string The complete template path.
	 */
	protected function get_template_path( $template_name ) {
		return apply_filters( "template:path",  trailingslashit( $this->get_template_directory() ) . '/' .$template_name . '.php' );
	}

	/**
	 * Checks to see if the template file exists.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string The template name to check.
	 *
	 * @return bool True if the template file exists, false otherwise.
	 */
	public function template_file_exists( $template_name ) {
		return file_exists( $this->get_template_path( $template_name ) );
	}

	/**
	 * Updates current depth and params, gets the template contents.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_name The template name.
	 * @param array  $params        The params to use in the template.
	 *
	 * @return false|string The template contents if the file exists, false otherwise.
	 */
	private function include_template( $template_name, $params ) {
		$this->depth++;

        $this->params[ $this->depth ] = apply_filters( "template:params", $params );

		$template_filter_args = [ 'template_name' => $template_name, 'path' => $this->get_template_path( $template_name ) ];

		ob_start();

        do_action( 'template:before_template', $template_filter_args );

        $this->include_file_with_scope( $this->get_template_path( $template_name ), [
            'template' => $this,
        ] );

        do_action( 'template:after_template', $template_filter_args );

		$result = ob_get_clean();

		unset( $this->params[ $this->depth ] );
		$this->depth--;

		return $result;
	}

    private function include_file_with_scope( $file, $scope ) {
        if ( file_exists( $file ) ) {
            extract( $scope );
            include $file;

            return true;
        }

        return false;
    }

}

