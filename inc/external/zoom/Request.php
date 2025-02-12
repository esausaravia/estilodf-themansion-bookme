<?php
namespace Bookme\Inc\External\Zoom;

use Bookme\Inc\Mains\Tables\Employee;

/**
 * Class Request
 */
class Request
{
    /** @var BaseAuth */
    protected $client;

    /** @var string */
    protected $api_point = 'https://api.zoom.us/v2/';

    /** @var array */
    protected $errors;

    /**
     * Request constructor.
     *
     * @param Employee|null $staff
     */
    public function __construct( $staff )
    {
        if ( $staff && ( $staff->get_zoom_api_key() && $staff->get_zoom_api_secret() )) {
            $this->client = JWT::create_for_staff( $staff );
        } else {
            $this->client = JWT::create_default();
        }
    }

    /**
     * Request
     *
     * @param string $http_method
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function request( $http_method, $method, array $fields = array() )
    {
        $url = $this->api_point . $method;
        $body = null;

        if ( $http_method == 'GET' ) {
            $url = add_query_arg( $fields, $url );
        } else {
            $body = json_encode( $fields, JSON_PRETTY_PRINT );
        }

        $response = wp_remote_request( $url, array(
            'method' => $http_method,
            'headers' => $this->client->headers(),
            'body' => $body,
        ) );

        return $this->result( $response );
    }

    /**
     * Get
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function get( $method, $fields = array() )
    {
        return $this->request( 'GET', $method, $fields );
    }

    /**
     * Post
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function post( $method, $fields )
    {
        return $this->request( 'POST', $method, $fields );
    }

    /**
     * Patch
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function patch( $method, $fields )
    {
        return $this->request( 'PATCH', $method, $fields );
    }

    /**
     * Put
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function put( $method, $fields )
    {
        return $this->request( 'PUT', $method, $fields );
    }

    /**
     * Delete
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function delete( $method, $fields = array() )
    {
        return $this->request( 'DELETE', $method, $fields );
    }

    /**
     * Result
     *
     * @param array|\WP_Error $response
     * @return array|false
     */
    protected function result( $response )
    {
        $this->errors = array();

        if ( ! is_wp_error( $response ) ) {
            if ( isset ( $response['body'] ) ) {
                $body = json_decode( (string) $response['body'], true );
                if ( $response['response']['code'] < 300 ) {
                    return $body !== null ? $body : true;
                } else {
                    if ( $body !== null ) {
                        if ( isset ( $body['message'] ) ) {
                            $this->errors[] = $body['message'];
                        } else {
                            $this->errors[] = __('Unknown error','bookme');
                        }
                    } else {
                        $this->errors[] = __('Invalid JSON','bookme');
                    }
                }
            } else {
                $this->errors[] = __('Empty body','bookme');
            }
        } else {
            $this->errors = $response->get_error_messages();
        }

        return false;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
}