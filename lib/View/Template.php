<?php


namespace Netdust\View;

use Netdust\Service\Logger\LoggerInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Render a view file with php.
 */
class Template  {


    /**
     * Template group.
     *
     * @var string
     */
    protected string $group = '';

    /**
     * Template root directory.
     *
     * @var string
     */
    protected string $template_root = '';

    /**
     * Collection of group template data.
     * @var array
     */
    protected array $globals;

    /**
     * Collection of template data.
     * @var array
     */
    protected array $data;

    /**
     * The current depth of this instance
     * @var int
     */
    private int $depth = 0;


    /**
     * Constructor.
     *
     * @param string $filepath
     * @param string $layout
     */
    public function __construct( string $template_root = '', array $globals = [] ) {
        $this->template_root = $template_root;
        $this->globals = $globals;
    }

    /**
     * Gets the specified template, if it is valid.
     *
     * @since 1.0.0
     *
     * @param $data  array of param values that can be used in the template via get_param().
     *
     * @return string The template contents.
     */
    public function render( string $template_name, array $data = [] ): string {

        if ( $this->exists( $template_name ) ) {
            $template = $this->include_template( $template_name , array_merge( $this->globals, $data ) );

        } else {
            $template_path = $this->get_path( $template_name  );

            app()->make( LoggerInterface::class )->error(
                "Template $template_name  was not loaded because the file located at $template_path does not exist.",
                'template_file_does_not_exist'
            );

            $template = '';
        }

        return $template;
    }

    /**
     * Checks to see if the template file exists.
     *
     * @param $template_name string The template name to check.
     *
     * @return bool True if the template file exists, false otherwise.
     */
    public function exists( string $template_name ): bool {
        return file_exists( $this->get_path( $template_name ) );
    }


    /**
     * Gets the template path, given the file name.
     *
     * @param $template_name string the template name to include.
     *
     * @return string The complete template path.
     */
    public function get_path( string $template_name ): string {
        return apply_filters( "template:path", trailingslashit($this->template_root) . $template_name . '.php' );
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
    private function include_template( string $template_name, array $data ): bool|string {
        $this->depth++;

        $this->data[ $this->depth ] = apply_filters( "template:params", $data );

        ob_start();

        do_action( 'template:before_template', [ 'template_name' => $template_name, 'path' => $this->get_path( $template_name ) ] );

        $this->include_file_with_scope( $this->get_path( $template_name ), [
            'template' => $this,
        ] );

        do_action( 'template:after_template', [ 'template_name' => $template_name, 'path' => $this->get_path( $template_name ) ] );

        $result = ob_get_clean();

        unset( $this->data[ $this->depth ] );
        $this->depth--;

        return $result;
    }

    private function include_file_with_scope( string $file, array $scope ): bool {
        if ( file_exists( $file ) ) {
            extract( $scope );
            include $file;

            return true;
        }

        return false;
    }


}