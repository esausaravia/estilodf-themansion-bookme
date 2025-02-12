<?php
namespace Bookme\Inc\Mains\Functions;

/**
 * Class Request
 */
abstract class Request
{

    /**
     * Get request parameter by name (first removing slashes).
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get_parameter($name, $default = null)
    {
        return self::has_parameter($name) ? stripslashes_deep($_REQUEST[$name]) : $default;
    }

    /**
     * Get all request parameters (first removing slashes).
     *
     * @return mixed
     */
    public static function get_parameters()
    {
        return stripslashes_deep($_REQUEST);
    }

    /**
     * Get all POST parameters (first removing slashes).
     *
     * @return mixed
     */
    public static function get_post_parameters()
    {
        return stripslashes_deep($_POST);
    }

    /**
     * Check if there is a parameter with given name in the request.
     *
     * @param string $name
     * @return bool
     */
    public static function has_parameter($name)
    {
        return array_key_exists($name, $_REQUEST);
    }
}