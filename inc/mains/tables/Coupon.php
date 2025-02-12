<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class Coupon
 */
class Coupon extends Inc\Core\Table
{
    /** @var  string */
    protected $code = '';
    /** @var  float */
    protected $discount = 0;
    /** @var  float */
    protected $deduction = 0;
    /** @var  int */
    protected $usage_limit = 1;
    /** @var  int */
    protected $used = 0;

    protected static $table = 'bm_coupons';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'code' => array('format' => '%s'),
        'discount' => array('format' => '%d'),
        'deduction' => array('format' => '%f'),
        'usage_limit' => array('format' => '%d'),
        'used' => array('format' => '%d'),
    );

    /**
     * Check service id is available in the coupon table
     *
     * @param array $service_ids
     * @return bool
     */
    public function valid(array $service_ids)
    {
        global $wpdb;
        return null !== $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM `" . CouponService::get_table_name() . "` 
                WHERE service_id IN (" . implode(',', $service_ids) . ") 
                    AND coupon_id = %d",
                    $this->get_id()
                ),
                ARRAY_A
            );

    }

    /**
     * Apply coupon
     *
     * @param $amount
     * @return float
     */
    public function apply($amount)
    {
        $amount = round($amount * (100 - $this->get_discount()) / 100 - $this->get_deduction(), 2);

        return $amount > 0 ? $amount : 0;
    }

    /**
     * Increase the number of used
     *
     * @param int $quantity
     */
    public function claim($quantity = 1)
    {
        $this->set_used($this->get_used() + $quantity);
    }

    /**
     * Get code
     *
     * @return string
     */
    public function get_code()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function set_code($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get discount
     *
     * @return float
     */
    public function get_discount()
    {
        return $this->discount;
    }

    /**
     * Set discount
     *
     * @param float $discount
     * @return $this
     */
    public function set_discount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get deduction
     *
     * @return float
     */
    public function get_deduction()
    {
        return $this->deduction;
    }

    /**
     * Set deduction
     *
     * @param float $deduction
     * @return $this
     */
    public function set_deduction($deduction)
    {
        $this->deduction = $deduction;

        return $this;
    }

    /**
     * Get usage_limit
     *
     * @return int
     */
    public function get_usage_limit()
    {
        return $this->usage_limit;
    }

    /**
     * Set usage_limit
     *
     * @param int $usage_limit
     * @return $this
     */
    public function set_usage_limit($usage_limit)
    {
        $this->usage_limit = $usage_limit;

        return $this;
    }

    /**
     * Get used
     *
     * @return int
     */
    public function get_used()
    {
        return $this->used;
    }

    /**
     * Set used
     *
     * @param int $used
     * @return $this
     */
    public function set_used($used)
    {
        $this->used = $used;

        return $this;
    }

}