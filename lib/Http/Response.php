<?php

namespace Netdust\Http;

class Response
{

    protected array $cookies = [];
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

    function __construct( int $status = 200, array $headers = [] ) {

        $this->statusCode = $status;
        $this->setHeaders($headers);
    }

    public function setHeaders( array $headers ): Response
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value);
        }
        return $this;
    }

    public function withBody( string $data ): Response
    {
        $this->body = $data;
        return $this;
    }

    public function write( string $data ): Response
    {
        $this->body .= $data;
        return $this;
    }

    public function withJson( $data, $status = null, $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ): Response {
        if (!is_string($json = @json_encode($data, $options))) {
            throw new \InvalidArgumentException(json_last_error_msg(), json_last_error());
        }

        $response = $this->withBody($json)->withHeader('Content-Type', 'application/json; charset=utf-8');

        return is_null($status) ? $response : $response->withStatus($status);
    }

    public function withstatus($status = 302): Response
    {
        $this->statusCode = $status;
        return $this;
    }
    public function withHeader(string $name, string $value): Response
    {
        $this->headers[strtolower($name)] = $value;
        return $this;
    }

    public function withRedirect($url, $status = 302): Response
    {
        return $this->withStatus($status)->withHeader('Location', (string) $url);
    }

    /**
     * Sets a response cookie.
     *
     */
    public function withCookie($name, $value = '', array $options = []): Response
    {
        $defaults = [
            'expire' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => false,
        ];

        $cookie = array_replace($defaults, $options);
        $cookie['value'] = strval($value);
        $cookie['expire'] = is_string($cookie['expire'])
            ? strtotime($cookie['expire'])
            : intval($cookie['expire']);

        $clone = clone $this;
        $clone->cookies[$name] = $cookie;

        return $clone;
    }

    /**
     * Sends the response.
     */
    public function send(): Response
    {
        if (!headers_sent()) {
            $this->sendHeaders();
        }

        echo $this->body;

        flush();

        return $this;
    }

    public function sendHeaders()
    {
        foreach ($this->headers as $name => $values) {
            header(sprintf('%s: %s', $name, implode(',', $values)));
        }

        foreach ($this->cookies as $name => $cookie) {
            setcookie(
                $name,
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }

        flush();

        return $this;
    }

    /**
     * Is this response informational?
     */
    public function isInformational(): bool
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is this response OK?
     */
    public function isOk(): bool
    {
        return $this->statusCode == 200;
    }

    /**
     * Is this response empty?
     */
    public function isEmpty(): bool
    {
        return in_array($this->statusCode, [204, 205, 304]);
    }

    /**
     * Is this response successful?
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is this response a redirect?
     */
    public function isRedirect(): bool
    {
        return in_array($this->statusCode, [301, 302, 303, 307]);
    }

    /**
     * Is this response a redirection?
     */
    public function isRedirection(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is this response forbidden?
     */
    public function isForbidden(): bool
    {
        return $this->statusCode == 403;
    }

    /**
     * Is this response not Found?
     */
    public function isNotFound(): bool
    {
        return $this->statusCode == 404;
    }

    /**
     * Is this response a client error?
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Is this response a server error?
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Returns the body as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->body;
    }
}
