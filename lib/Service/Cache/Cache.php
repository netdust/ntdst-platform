<?php
/**
 * Registers a shortcode
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Netdust\Service\Cache;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Cache
 *
 */
class Cache {

    /**
     * @param $key
     * @param $data
     */
    public function create(string $key, mixed $data, int $expiration=3600): void
    {
        set_transient($key, $data, $expiration);
    }

    /**
     * @param $key
     *
     * @return array|false|mixed
     */
    public function get( string $key ): mixed
    {
        if (true === apply_filters('netdust_enable_cache', false)) {
            return get_transient($key);
        }
        return array();
    }

    /**
     * @param $key
     */
    public function delete_transient(string $key): void
    {
        delete_transient($key);
    }

}