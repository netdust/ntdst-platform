<?php

/**
 *
 * @link              https://netdust.be
 * @since             1.0.0-dev
 * @package           Netdust\Ndst
 * @author            Stefan Vandermeulen
 *
 * @wordpress-plugin
 * Plugin Name:       NTDST Wordpress Library
 * Plugin URI:        https://netdust.be
 * Description:       A framework for Online Wordpress Applications.
 * Version:           3.0.0
 * Author:            Stefan Vandermeulen
 * Author URI:        https://netdust.be
 * Text Domain:       ntdst_platform
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'NTDST_APPLICATION' ) ) {
    define( 'NTDST_APPLICATION', 'ntdst_application' );
}

// make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

/**
 * first setup autoloader, lazy like i like it
 */
require_once 'lib/Utils/AutoLoader.php';

\Netdust\Utils\AutoLoader::setup_autoloader( [
    'Psr\Container\\'=> dirname( __FILE__ ).'/vendor/psr/container/src/',
    'lucatume\DI52\\'=> dirname( __FILE__ ).'/vendor/di52-master/src/',
    'AltoRouter'=> dirname( __FILE__ ).'/vendor/AltoRouter-master/',
    'Netdust\\'=> dirname( __FILE__ ).'/lib/'
] );

/**
 * easy access throughout application
 */
if ( ! function_exists( 'app' ) ) {
    function app( $id = NTDST_APPLICATION ) {
        return \Netdust\App::container()->get( $id );
    }
}

do_action('netdust_platform_loaded');