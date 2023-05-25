<?php
/**

 */

namespace Netdust\Traits;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Feature Extension Trait.
 *
 * @since   1.3.0
 */
trait Cache
{


    /**
     * @param $key
     * @param $data
     */
    public static function create_cache($key, $data, $expiration=3600)
    {
        set_transient($key, $data, $expiration);
    }

    /**
     * @param $key
     *
     * @return array|false|mixed
     */
    public static function get_cache($key)
    {
        if (true === apply_filters('netdust_enable_cache', false)) {
            return get_transient($key);
        }
        return array();
    }

    /**
     * @param $key
     */
    public static function delete_transient($key)
    {
        delete_transient($key);
    }
}