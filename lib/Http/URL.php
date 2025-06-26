<?php

namespace Netdust\Http;

use Netdust\Logger\Logger;
use Netdust\Utils\Arr;

class URL {


    /**
     * Get the request's pathname
     *
     * @return string
     */
    public static function pathname()
    {
        $uri = $_SERVER['REQUEST_URI']??'/';
        // Strip the query string from the URI
        return strstr($uri, '?', true) ?: $uri;
    }


    public static function basePath() {
        $path = ltrim(static::pathname(), '/');

        $pathSegments = explode('/', $path);

        return '/' . reset($pathSegments);
    }


    public static function getLastPathSegment($url) {
        $path = parse_url($url, PHP_URL_PATH); // to get the path from a whole URL
        $pathTrimmed = trim($path, '/'); // normalise with no leading or trailing slash
        $pathTokens = explode('/', $pathTrimmed); // get segments delimited by a slash

        return end($pathTokens); // get the last segment
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