<?php

namespace Netdust\Core;

use Netdust\ApplicationInterface;

Final Class Requirements {

    protected ApplicationInterface $app;
    protected array $requirements;
    protected array $errors = [];

    public function __construct( ApplicationInterface $app, array $requirements ) {
        $this->app = $app;
        $this->requirements = $requirements;
    }

    public function satisfied(): bool {
        $this->check_php(  $this->requirements['php'] );
        $this->check_wp(  $this->requirements['wp'] );
        $this->check_theme(  $this->requirements['theme'] );
        $this->check_plugins(  $this->requirements['plugins'] );


        return empty( $this->errors );
    }

    public function print_notice():void {
        add_action( 'admin_notices', function() {

            $class = 'notice notice-error';
            $message = sprintf(
                '<p>' . __( '<strong>%1$s</strong> cannot be activated because it requires:', $this->app->text_domain ) . '</p>',
                esc_html( $this->name )
            );

            $errors = '';
            foreach ( $this->errors as $error ) {
                $errors .= '<li>' . $error . '</li>';
            }

            printf( '<div class="%1$s">%2$s<ul>%3$s</ul></div>', esc_attr( $class ), esc_html( $message ), esc_html( $errors ) );

        } );
    }

    public function add_error( string $error_message ): void {

        $this->errors[] = $error_message;
    }

    public function check_php( int $version ):void  {

        if ( version_compare( phpversion(), $version, '<' ) ) {
            $this->add_error( sprintf( __( 'Minimum required version of PHP is %s. Your version is %s', $this->app->text_domain ), $version, phpversion() ) );
        }

    }

    public function check_wp( int $version ):void {

        if ( version_compare( get_bloginfo( 'version' ), $version, '<' ) ) {
            $this->add_error( sprintf( __( 'Minimum required version of WordPress is %s. Your version is %s', $this->app->text_domain ), $version, get_bloginfo( 'version' ) ) );
        }

    }

    public function check_theme( string $theme ):void {

        $current_theme = wp_get_theme();

        if ( !empty($theme) && $current_theme->get_template() != $theme ) {
            $this->add_error( sprintf( __( 'Required theme: %s', $this->app->text_domain ), $theme ) );
        }

    }

    public function check_plugins( array $plugins ):void {

        $active_plugins_raw = wp_get_active_and_valid_plugins();

        if ( is_multisite() ) {
            $active_plugins_raw = array_merge( $active_plugins_raw, wp_get_active_network_plugins() );
        }

        $active_plugins          = array();
        $active_plugins_versions = array();

        foreach ( $active_plugins_raw as $plugin_full_path ) {
            $plugin_file                             = str_replace( WP_PLUGIN_DIR . '/', '', $plugin_full_path );
            $active_plugins[]                        = $plugin_file;
            $plugin_api_data                         = @get_file_data( $plugin_full_path, array( 'Version' ) );
            $active_plugins_versions[ $plugin_file ] = $plugin_api_data[0];
        }

        foreach ( $plugins as $plugin_file => $plugin_data ) {

            if ( ! in_array( $plugin_file, $active_plugins ) ) {
                $this->add_error( sprintf( __( 'Required plugin: %s', $this->app->text_domain ), $plugin_data['name'] ) );
            } else if ( version_compare( $active_plugins_versions[ $plugin_file ], $plugin_data['version'], '<' ) ) {
                $this->add_error( sprintf( __( 'Minimum required version of %s plugin is %s. Your version is %s', $this->app->text_domain ), $plugin_data['name'], $plugin_data['version'], $active_plugins_versions[ $plugin_file ] ) );
            }

        }

    }

}