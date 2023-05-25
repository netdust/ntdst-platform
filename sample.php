<?php


/**
 *
 * @link              https://netdust.be
 * @since             1.0.0-dev
 * @package           Netdust\Vad
 * @author            Stefan Vandermeulen
 *
 * @wordpress-plugin
 * Plugin Name:       VAD Online Platform
 * Plugin URI:        https://netdust.be
 * Description:       A framework for VAD Online Wordpress Applications.
 * Version:           3.0.0
 * Author:            Stefan Vandermeulen
 * Author URI:        https://netdust.be
 * Text Domain:       vad_platform
 */

/**
 * @todo FAQ op thema pagina's met structured data
 * @todo auto redirect na veranderen slug 301
 * @todo thema's opsplitsen voor de doorverwijsgids fiches en optie andere toevoegen
 * @todo vroeginterventie doelgroepen tekstvelden toevoegen
 * @todo preventie en vroeginterventie zoekveld weglaten, enkel postcodes
 *
 * @todo teaser for products, order for products
 * @todo pagina links veranderen naar tools voor preventiewerk
 * @todo content links = 1 artikel
 */


defined('ABSPATH') || exit;

use lucatume\DI52\Container;
use Netdust\App;
use Netdust\Utils\Logger\Logger;
use Netdust\Utils\Logger\LoggerInterface;
use Netdust\Utils\Logger\SimpleLogger;
use Netdust\Utils\Router\Router;
use Netdust\Utils\Router\SimpleRouter;
use Netdust\Utils\Router\RouterInterface;

// make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('APP_PLUGIN_FILE', __FILE__);

\Netdust\Utils\AutoLoader::setup_autoloader([
    'Netdust\VAD\\' => dirname(__FILE__) . '/app/src/',
    'Netdust\VAD\Services\\' => dirname(__FILE__) . '/services/'
]);

add_filter('netdust_disable_cache', '__return_true');

function app($id = APP_PLUGIN_FILE)
{
    global $container;
    return $container->get($id);
}

function vad_is_website()
{
    $url = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

    if (strpos($url, 'vormingen') !== false) {
        return FALSE;
    }

    return TRUE;
}


$container = new Container();

//bind router
$container->bind(RouterInterface::class, new SimpleRouter());
Router::setRouter($container->get(RouterInterface::class));

//bind logger
$container->bind(LoggerInterface::class, SimpleLogger::class);
Logger::setLogger($container->get(LoggerInterface::class));
Logger::logger()->log_level = 'debug';

//bind application
$container->singleton(APP_PLUGIN_FILE, new \Netdust\ApplicationProvider($container, [
    'file' => __FILE__,
    'text_domain' => 'ntdst',
    'version' => '1.1.2',
    'minimum_wp_version' => '6.0',
    'minimum_php_version' => '7.4',
    'build_path' => '/app'
]));

App::setApplication($container->get(APP_PLUGIN_FILE));
$container->register(APP_PLUGIN_FILE);

/*
class UserController {
    public function index( string $action, int $id ) {
        echo $action;
        echo $id;
    }
}

Router::map(
    ['GET'],
    'testing/[:action]/[i:id]',
    $container->callback( UserController::class, 'index'), 'test'
);

//echo '--' . Router::generate( 'test', ['action'=>'update', 'id'=>20] ); */


