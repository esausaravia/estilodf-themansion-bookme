<?php
namespace Bookme\Inc\External\Zoom;

use Bookme\Inc\External\Zoom\Jwt\JWT as JWTLib;
use Bookme\Inc\Mains\Tables\Employee;

/**
 * Class JWT
 */
class JWT
{
    /** @var string */
    protected $api_key;
    /** @var string */
    protected $api_secret;

    /**
     * Init instance
     *
     * @param Employee|null $staff
     */
    protected function init( $staff = null )
    {
        if ( $staff ) {
            $this
                ->set_api_key( $staff->get_zoom_api_key() )
                ->set_api_secret( $staff->get_zoom_api_secret() );
        } else {
            $this
                ->set_api_key( get_option('bookme_zoom_api_key') )
                ->set_api_secret( get_option('bookme_zoom_api_secret') );
        }
    }

    /**
     * Create auth instance personally for staff
     *
     * @param Employee $staff
     * @return static
     */
    public static function create_for_staff( Employee $staff )
    {
        $auth = new static();
        $auth->init( $staff );

        return $auth;
    }

    /**
     * Create default auth instance (with global settings)
     *
     * @return static
     */
    public static function create_default()
    {
        $auth = new static();
        $auth->init();

        return $auth;
    }

    /**
     * Headers
     *
     * @return array
     */
    public function headers()
    {
        return array(
            'Authorization' => 'Bearer ' . $this->get_bearer_token(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        );
    }

    /**
     * Sets jwt_api_key
     *
     * @param string $api_key
     * @return $this
     */
    public function set_api_key( $api_key )
    {
        $this->api_key = $api_key;

        return $this;
    }

    /**
     * Sets jwt_api_secret
     *
     * @param string $api_secret
     * @return $this
     */
    public function set_api_secret( $api_secret )
    {
        $this->api_secret = $api_secret;

        return $this;
    }

    /**
     * Create Bearer token
     *
     * @return string
     */
    protected function get_bearer_token()
    {
        $token = array(
            'iss' => $this->api_key,
            'exp' => time() + 60,
        );

        return JWTLib::encode( $token, $this->api_secret );
    }

}