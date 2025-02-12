<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class EmployeePreferenceOrder
 */
class EmployeePreferenceOrder extends Inc\Core\Table
{
    /** @var  int */
    protected $service_id;
    /** @var  int */
    protected $staff_id;
    /** @var  int */
    protected $position = 9999;

    protected static $table = 'bm_employee_preference_orders';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'service_id' => array('format' => '%d', 'reference' => array('table' => 'Service')),
        'staff_id' => array('format' => '%d', 'reference' => array('table' => 'Employee')),
        'position' => array('format' => '%d'),
    );

    /**
     * Gets service_id
     *
     * @return int
     */
    public function get_service_id()
    {
        return $this->service_id;
    }

    /**
     * Sets service_id
     *
     * @param int $service_id
     * @return $this
     */
    public function set_service_id($service_id)
    {
        $this->service_id = $service_id;

        return $this;
    }

    /**
     * Gets staff_id
     *
     * @return int
     */
    public function get_staff_id()
    {
        return $this->staff_id;
    }

    /**
     * Sets staff_id
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
     * Gets position
     *
     * @return int
     */
    public function get_position()
    {
        return $this->position;
    }

    /**
     * Sets position
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