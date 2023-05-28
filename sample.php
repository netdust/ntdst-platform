<?php


defined('ABSPATH') || exit;

// make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}


//boot application
\Netdust\App::boot( NTDST_APPLICATION, [
    'file'                => __FILE__,
    'text_domain'         => 'ntdst',
    'version'             => '1.1.2',
    'minimum_wp_version'  => '6.0',
    'minimum_php_version' => '7.4',
    'build_path'          => '/app'
] );


