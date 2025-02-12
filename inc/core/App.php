<?php
namespace Bookme\Inc\Core;

/**
 * Class App
 */
abstract class App
{

    /**
     * Array of child class instances
     * @var App[]
     */
    private static $instances = array();

    /**
     * Get class instance.
     * @return static
     */
    public static function get_instance()
    {
        $class = get_called_class();
        if ( ! isset ( self::$instances[ $class ] ) ) {
            self::$instances[ $class ] = new $class();
        }

        return self::$instances[ $class ];
    }

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {
        Ajax::register_ajax_actions($this);
    }

    /**
     * Protected Constructor.
     */
    protected function __construct(){
        // register ajax
        $this->register_ajax();
    }
}