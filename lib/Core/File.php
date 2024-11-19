<?php
/**
 * File class
 * Locates plugin's files
 */
namespace Netdust\Core;


use Netdust\Traits\Singleton;

class File {


    /**
     * Plugin file absolute path
     * @var string
     */
    public string $file;

    /**
     * Assets directory name with a slash at the end
     * @var string
     */
    public string $root;

    /**
     * Class constructor
     * @param string $file       full path to main plugin file
     * @param array  $root       dir where to go and look
     */
    public function __construct( string $file = __FILE__, string $root_path = '' ) {
        $this->file = $file;
        $this->root = $root_path;
    }

    /**
     * Builds the dir name from an array of parts
     * @uses   trainlingslashit()
     * @param  array  $parts parts of the path
     * @return string        dir name
     */
    public function build_dir_from_array( array $parts = array() ): string {

        $dir = '';

        foreach ( $parts as $part ) {
            $dir .= trailingslashit( $part );
        }

        return $dir;

    }

    /**
     * Resolves file path
     * You can provide a file string or an array of dirs and file name at the end
     * @param  mixed  $file file structure
     * @return string       full file path
     */
    public function resolve_file_path( string|array $file = '' ): string {

        if ( is_array( $file ) ) {
            $filename = array_pop( $file );
            $file     = $this->build_dir_from_array( $file ) . $filename;
        }

        return $file;

    }


    /**
     * Gets the plugin root dir absolute path
     * @return string path
     */
    public function plugin_path(): string {
        return plugin_dir_path( $this->file );
    }

    /**
     * Gets the plugin root dir url
     * @return string url
     */
    public function plugin_url(): string {
        return plugin_dir_url( $this->file );
    }

    /**
     * Gets file path which is relative to plugin root path
     * @param  mixed $file if it's an array, the dir structure will be built
     * @return string      file absolute path
     */
    public function file_path( string|array $file = '' ): string {
        return $this->plugin_path() . $this->resolve_file_path( $file );
    }

    /**
     * Gets file url which is relative to plugin root
     * @param  mixed $file if it's an array, the dir structure will be built
     * @return string      file url
     */
    public function file_url( string|array $file = '' ): string {
        return $this->plugin_url() . $this->resolve_file_path( $file );
    }

    /**
     * Gets dir path which is relative to plugin root path
     * @param  mixed $dir if it's an array, the dir structure will be built
     * @return string     dir absolute path
     */
    public function dir_path( string|array $dir = '' ): string  {
        return $this->plugin_path() . $this->build_dir_from_array( (array) $dir );
    }

    /**
     * Gets dir url which is relative to plugin root
     * @param  mixed $dir if it's an array, the dir structure will be built
     * @return string     dir url
     */
    public function dir_url( string|array $dir = '' ): string {
        return $this->plugin_url() . $this->build_dir_from_array( (array) $dir );
    }

    /**
     * Gets url to an asset file
     * @param  string $type asset type - js | css | image
     * @param  string $file file name
     * @return string       asset file url
     */
    public function asset_url( string $type = '', string $file = '' ): string {
        return $this->file_url( [$this->root, 'assets',$type,$file] );
    }

    /**
     * Gets path to an asset file
     * @param  string $type asset type - js | css | images
     * @param  string $file file name
     * @return string       asset file path
     */
    public function asset_path( string $type = '', string $file = '' ): string {
        return $this->file_path( [$this->root, 'assets',$type,$file] );
    }

    /**
     * Gets url to a template file
     * @param  string $file file name
     * @return string       template file url
     */
    public function template_url( string $type = '', string $file = '' ): string {
        return $this->file_url( [$this->root, 'templates',$type,$file] );
    }

    /**
     * Gets path to a template file
     * @param  string $file file name
     * @return string       template file path
     */
    public function template_path( string $type = '', string $file = '' ): string {
        return $this->file_path( [$this->root, 'templates',$type,$file] );
    }

}
