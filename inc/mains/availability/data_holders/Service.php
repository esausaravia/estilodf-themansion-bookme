<?php
namespace Bookme\Inc\Mains\Availability\DataHolders;

/**
 * Class Service
 */
class Service
{
    /** @var double */
    protected $price;
    /** @var int */
    protected $capacity_min;
    /** @var int */
    protected $capacity_max;
    /** @var string */
    protected $employee_preference_rule;
    /** @var  int */
    protected $employee_preference_order;

    /**
     * Constructor.
     *
     * @param double $price
     * @param int    $capacity_min
     * @param int    $capacity_max
     * @param string $employee_preference_rule
     * @param int    $employee_preference_order
     */
    public function __construct( $price, $capacity_min, $capacity_max, $employee_preference_rule, $employee_preference_order )
    {
        $this->price        = (double) $price;
        $this->capacity_min = (int) $capacity_min;
        $this->capacity_max = (int) $capacity_max;
        $this->employee_preference_rule  = $employee_preference_rule;
        $this->employee_preference_order = $employee_preference_order;
    }

    /**
     * Gets staff preference rule
     *
     * @return string
     */
    public function get_employee_preference_rule()
    {
        return $this->employee_preference_rule;
    }

    /**
     * Gets staff preference order
     *
     * @return int
     */
    public function get_employee_preference_order()
    {
        return $this->employee_preference_order;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function price()
    {
        return $this->price;
    }

    /**
     * Get capacity min.
     *
     * @return int
     */
    public function capacity_min()
    {
        return $this->capacity_min;
    }

    /**
     * Get capacity max.
     *
     * @return int
     */
    public function capacity_max()
    {
        return $this->capacity_max;
    }
}