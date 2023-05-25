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

if ( ! defined( 'NTDST_PLUGIN_FILE' ) ) {
    define( 'NTDST_PLUGIN_FILE', __FILE__ );
}

// make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

/**
 * first setup autoloader, easy lazy like i like it
 */
require_once 'lib/Utils/AutoLoader.php';

\Netdust\Utils\AutoLoader::setup_autoloader( [
    'Psr\Container\\'=> dirname( __FILE__ ).'/vendor/psr/container/src/',
    'lucatume\DI52\\'=> dirname( __FILE__ ).'/vendor/di52-master/src/',
    'AltoRouter'=> dirname( __FILE__ ).'/vendor/AltoRouter-master/',
    'Netdust\\'=> dirname( __FILE__ ).'/lib/'
] );


/**
 * using this framework, always use singleton if you need to retrieve object later
 * binding will always create a new instance, even if id stays the same


require ('vendor/require.php' );
require_once 'lib/App.php';

use lucatume\DI52\Container;
use \Netdust\App;

$container = new Container();

$container->when( NTDST_PLUGIN_FILE )->needs('$args')->give( [
    'file'                => __FILE__,
    'text_domain'         => 'ntdst',
    'version'             => '1.1.1',
    'minimum_wp_version'  => '6.0',
    'minimum_php_version' => '7.4',
    'build_path'          => '/app'
]);

$container->singleton( NTDST_PLUGIN_FILE , App::class );
$container->register( NTDST_PLUGIN_FILE );

function app( ) {
    global $container;
    return $container->get( NTDST_PLUGIN_FILE );
}
 */