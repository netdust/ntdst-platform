<?php


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

function app($id = APP_PLUGIN_FILE)
{
    global $container;
    return $container->get($id);
}

$container = new Container();

//bind router
$container->bind(RouterInterface::class, new SimpleRouter());
Router::setRouter($container->get(RouterInterface::class));

//bind logger
$container->bind(LoggerInterface::class, SimpleLogger::class);
Logger::setLogger($container->get(LoggerInterface::class));

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


