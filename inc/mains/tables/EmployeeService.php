<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class EmployeeService
 */
class EmployeeService extends Inc\Core\Table
{
    /** @var  int */
    protected $staff_id;
    /** @var  int */
    protected $service_id;
    /** @var  float */
    protected $price = 0;
    /** @var  int */
    protected $capacity_min = 1;
    /** @var  int */
    protected $capacity_max = 1;

    /** @var Service */
    public $service;

    protected static $table = 'bm_employee_services';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'staff_id' => array('format' => '%d', 'reference' => array('table' => 'Employee')),
        'service_id' => array('format' => '%d', 'reference' => array('table' => 'Service')),
        'price' => array('format' => '%f'),
        'capacity_min' => array('format' => '%d'),
        'capacity_max' => array('format' => '%d')
    );

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
     * @param Employee $staff
     * @return $this
     */
    public function set_staff(Employee $staff)
    {
        return $this->set_staff_id($staff->get_id());
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
     * @param Service $service
     * @return $this
     */
    public function set_service(Service $service)
    {
        return $this->set_service_id($service->get_id());
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
     * Gets price
     *
     * @return float
     */
    public function get_price()
    {
        return $this->price;
    }

    /**
     * Sets price
     *
     * @param float $price
     * @return $this
     */
    public function set_price($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Gets capacity_min
     *
     * @return int
     */
    public function get_capacity_min()
    {
        return $this->capacity_min;
    }

    /**
     * Sets capacity_min
     *
     * @param int $capacity_min
     * @return $this
     */
    public function set_capacity_min($capacity_min)
    {
        $this->capacity_min = $capacity_min;

        return $this;
    }

    /**
     * Gets capacity_max
     *
     * @return int
     */
    public function get_capacity_max()
    {
        return $this->capacity_max;
    }

    /**
     * Sets capacity_max
     *
     * @param int $capacity_max
     * @return $this
     */
    public function set_capacity_max($capacity_max)
    {
        $this->capacity_max = $capacity_max;

        return $this;
    }
}