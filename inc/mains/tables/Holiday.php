<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class Holiday
 */
class Holiday extends Inc\Core\Table
{
    /** @var  int */
    protected $staff_id;
    /** @var  int */
    protected $parent_id;
    /** @var  string */
    protected $date;
    /** @var  int */
    protected $repeat_event;

    protected static $table = 'bm_holidays';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'staff_id' => array('format' => '%d', 'reference' => array('table' => 'Employee')),
        'parent_id' => array('format' => '%d'),
        'date' => array('format' => '%s'),
        'repeat_event' => array('format' => '%s'),
    );

    /**
     * Get staff_id
     *
     * @return int
     */
    public function get_staff_id()
    {
        return $this->staff_id;
    }

    /**
     * Set staff_id
     *
     * @param int $staff_id
     * @return $this
     */
    public function set_staff_id($staff_id)
    {
        $this->staff_id = $staff_id;

        return $this;
    }

    /**
     * Get parent_id
     *
     * @return int
     */
    public function get_parent_id()
    {
        return $this->parent_id;
    }

    /**
     * Set parent
     *
     * @param Holiday $parent
     * @return $this
     */
    public function set_parent(Holiday $parent)
    {
        return $this->set_parent_iId($parent->get_id());
    }

    /**
     * Set parent_id
     *
     * @param int $parent_id
     * @return $this
     */
    public function set_parent_iId($parent_id)
    {
        $this->parent_id = $parent_id;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function get_date()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param string $date
     * @return $this
     */
    public function set_date($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get repeat_event
     *
     * @return int
     */
    public function get_repeat_event()
    {
        return $this->repeat_event;
    }

    /**
     * Set repeat_event
     *
     * @param int $repeat_event
     * @return $this
     */
    public function set_repeat_event($repeat_event)
    {
        $this->repeat_event = $repeat_event;

        return $this;
    }

}
