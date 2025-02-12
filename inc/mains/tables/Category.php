<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class Category
 */
class Category extends Inc\Core\Table
{
    /** @var  string */
    protected $name;
    /** @var  int */
    protected $position = 9999;

    protected static $table = 'bm_categories';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'name' => array('format' => '%s'),
        'position' => array('format' => '%d'),
    );

    /**
     * @var Service[]
     */
    private $services;

    /**
     * Save category
     *
     * @return false|int
     */
    public function save()
    {
        $return = parent::save();
        if ($this->is_loaded()) {
            // Register string for translate in WPML.
            do_action('wpml_register_single_string', 'bookme', 'category_' . $this->get_id(), $this->get_name());
        }
        return $return;
    }

    /**
     * @param null $locale
     * @return string
     */
    public function get_translated_name($locale = null)
    {
        return Inc\Mains\Functions\System::get_translated_string('category_' . $this->get_id(), $this->get_name(), $locale);
    }

    /**
     * @param Service $service
     */
    public function add_service(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * @return Service[]
     */
    public function get_services()
    {
        return $this->services;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function get_position()
    {
        return $this->position;
    }

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position)
    {
        $this->position = $position;

        return $this;
    }
}
