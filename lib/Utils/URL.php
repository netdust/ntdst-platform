<?php

namespace Netdust\Utils;

class URL {

    /**
     * Is the request secure?
     *
     * @return boolean
     */
    public static function isSecure()
    {
        return (Arr::get($_SERVER, 'HTTPS') == true);
    }

    /**
     * Gets the request IP address
     *
     * @return string
     */
    public static function ip()
    {
        return Arr::get($_SERVER, 'REMOTE_ADDR');
    }

    /**
     * Gets the request URI
     *
     * @return string
     */
    public static function uri()
    {
        return Arr::get($_SERVER, 'REQUEST_URI', '/');
    }

    /**
     * Gets the request Method
     *
     * @return string
     */
    public static function method()
    {
        return Arr::get($_SERVER, 'REQUEST_METHOD', '/');
    }

    /**
     * Get the request's pathname
     *
     * @return string
     */
    public static function pathname()
    {
        $uri = static::uri();

        // Strip the query string from the URI
        $uri = strstr($uri, '?', true) ?: $uri;

        return $uri;
    }


    public static function basePath() {
        $path = ltrim(static::pathname(), '/');

        $pathSegments = explode('/', $path);

        return '/' . reset($pathSegments);
    }

    /**
     * String manupilations
     */
    public static function removeTrailingSlash($input)
    {
        return rtrim($input, '/\\');
    }

    public static function addTrailingSlash($input)
    {
        return static::removeTrailingSlash($input) . '/';
    }

    public static function removeLeadingSlash($input)
    {
        return ltrim($input, '/\\');
    }

    public static function addLeadingSlash($input)
    {
        return '/' . static::removeLeadingSlash($input);
    }

    public static function addLeadingTrailingSlash($input)
    {
        return static::addTrailingSlash( '/' . static::removeLeadingSlash($input) );
    }

}