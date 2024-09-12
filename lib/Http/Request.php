<?php

namespace Netdust\Http;

use Netdust\Traits\Collection;

final class Request
{
    use Collection;

    /**
     * @var array
     */
    private array $storage = [
        'scheme'       => 'http',
        'host'         => '',
        'path'         => '/',
        'query_string' => '',
        'query_method' => '',
        'http_referer' => '',
    ];

    /**
     * @var array
     */
    private array $server;

    /**
     * @var bool
     */
    private bool $parsed = false;

    /**
     * Request constructor.
     *
     * @param array $server
     */
    public function __construct( ?array $server = null )
    {
        $this->server = $server ?? $_SERVER;
        $this->marshallFromServer();
    }

    public function getUri(): string
    {
        $this->parsed or $this->marshallFromServer();

        return $this->storage['uri'];
    }

    public function getScheme(): string
    {
        $this->parsed or $this->marshallFromServer();

        return $this->storage['scheme'];
    }


    public function getHost(): string
    {
        $this->parsed or $this->marshallFromServer();

        return $this->storage['host'];
    }


    public function getPort(): string
    {
        return '';
    }

    public function getPath(): string
    {
        $this->parsed or $this->marshallFromServer();

        return $this->storage['path'];
    }

    public function getMethod(): string
    {
        $this->parsed or $this->marshallFromServer();

        return $this->storage['request_method'];
    }

    public function getQuery(): string
    {
        $this->parsed or $this->marshallFromServer();

        return $this->storage['query_string'];
    }

    public function getReferer(): string
    {
        $this->parsed or $this->marshallFromServer();

        return $this->storage['http_referer'];
    }

    public function hasQuery(): bool
    {
        $query_string = $this->getQuery();

        return !empty($query_string);
    }


    public function chunks()
    {
        $path = $this->path();

        return $path === '/' ? [] : explode('/', $path);
    }

    public function endpoint()
    {
        $chunks = $this->chunks();

        return end( $chunks );
    }


    /**
     * Parse server array to find url components.
     */
    private function marshallFromServer()
    {
        $uri = $this->server['REQUEST_URI'];

        $scheme = is_ssl() ? 'https' : 'http';

        $host = $this->marshallHostFromServer() ? : parse_url(home_url(), PHP_URL_HOST);
        $host = trim($host, '/');

        $pathArray = explode('?', $this->marshallPathFromServer(), 2);
        $path = trim($pathArray[0], '/');

        empty($path) and $path = '/';

        $query_string = '';
        if (isset($this->server['QUERY_STRING'])) {
            $query_string = ltrim($this->server['QUERY_STRING'], '?');
        }

        $request_method = '';
        if (isset($this->server['REQUEST_METHOD'])) {
            $request_method = strtoupper($this->server['REQUEST_METHOD']);
        }

        $http_referer = '';
        if (isset($this->server['HTTP_REFERER'])) {
            $http_referer = strtoupper($this->server['HTTP_REFERER']);
        }

        parse_str($this->getQuery(), $this->collection);

        $this->storage = compact('uri', 'scheme', 'host', 'path', 'query_string', 'request_method', 'http_referer');
        $this->parsed = true;
    }

    /**
     * Parse server array to find url host.
     *
     * Contains code from Zend\Diactoros\ServerRequestFactory
     *
     * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD
     *            License
     *
     * @return string
     */
    private function marshallHostFromServer()
    {
        $host = isset($this->server['HTTP_HOST']) ? $this->server['HTTP_HOST'] : '';
        if (empty($host)) {
            return isset($this->server['SERVER_NAME']) ? $this->server['SERVER_NAME'] : '';
        }

        if (is_string($host) && preg_match('|\:(\d+)$|', $host, $matches)) {
            $host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
        }

        return $host;
    }

    /**
     * Parse server array to find url path.
     *
     * Contains code from Zend\Diactoros\ServerRequestFactory
     *
     * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD
     *            License
     *
     * @return string
     */
    private function marshallPathFromServer()
    {
        $get = function ($key, array $values, $default = null) {
            return array_key_exists($key, $values) ? $values[$key] : $default;
        };

        // IIS7 with URL Rewrite: make sure we get the unencoded url
        // (double slash problem).
        $iisUrlRewritten = $get('IIS_WasUrlRewritten', $this->server);
        $unencodedUrl = $get('UNENCODED_URL', $this->server, '');
        if ('1' == $iisUrlRewritten && ! empty($unencodedUrl)) {
            return $unencodedUrl;
        }

        $requestUri = $get('REQUEST_URI', $this->server);

        // Check this first so IIS will catch.
        $httpXRewriteUrl = $get('HTTP_X_REWRITE_URL', $this->server);
        if ($httpXRewriteUrl !== null) {
            $requestUri = $httpXRewriteUrl;
        }

        // Check for IIS 7.0 or later with ISAPI_Rewrite
        $httpXOriginalUrl = $get('HTTP_X_ORIGINAL_URL', $this->server);
        if ($httpXOriginalUrl !== null) {
            $requestUri = $httpXOriginalUrl;
        }

        if ($requestUri !== null) {
            return preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
        }

        $origPathInfo = $get('ORIG_PATH_INFO', $this->server);

        return empty($origPathInfo) ? '/' : $origPathInfo;
    }
}