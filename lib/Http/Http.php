<?php

namespace Netdust\Http;


use Netdsut\Http\RestAPI;
use Netdust\App;
use Netdust\Logger\LoggerInterface;

/**
 * Controller class for everything to do with request and response.
 */
class Http {


    public function getRequest(): Request
    {
        return App::get( Request::class );
    }

    public function getResponse(): Response
    {
        return App::get( Response::class );
    }

    public function getRestApi(): RestAPI
    {
        return App::get( RestAPI::class );
    }



    public static function get( $url, $data = [], $args = [], $headers = [] ): array {

        try {
            return ( new self() )->request( 'GET', $url, $data, $args, $headers );
        } catch( \Exception $e) {
            return [];
        }
    }

    public static function post( $url, $data = [], $args = [], $headers = [] ): array {

        try {
            return ( new self() )->request( 'POST', $url, $data, $args, $headers );
        } catch( \Exception $e) {
            return [];
        }
    }

    protected function request( string $method, string $url, array $args = [], array $headers = [], bool $json=true): array
    {

        switch ($method) {
            case 'GET':
                $query = http_build_query($args, '', '&');
                $response = wp_remote_request("{$url}?{$query}", compact('headers'));
                break;
            case 'POST':
                $options = compact('headers');
                $options['method'] = 'POST';
                $options['body'] = $json ? json_encode($args):$args;

                $response = wp_remote_request($url, $options);
                if (is_wp_error($response)) {
                    $error = App::get( LoggerInterface::class )->error( $response->get_error_message() );
                    throw new \Exception($error['message']);
                }
                break;

            default:
                $error = App::get( LoggerInterface::class )->error( "Call to undefined method {$method}" );
                throw new \Exception($error['message']);
        }


        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);


        $encoded = json_decode($body, true);
        $success = $code >= 200 && $code <= 299 && $encoded;

        return [
            'success' => $success,
            'data' => $success ? $encoded : 'error',
        ];
    }
}