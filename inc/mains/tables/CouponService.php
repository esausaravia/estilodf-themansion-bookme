<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class CouponService
 */
class CouponService extends Inc\Core\Table
{
    /** @var  int */
    protected $coupon_id = 0;
    /** @var  int */
    protected $service_id = 0;

    protected static $table = 'bm_coupons_to_services';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'coupon_id' => array('format' => '%d', 'reference' => array('table' => 'Coupon')),
        'service_id' => array('format' => '%d', 'reference' => array('table' => 'Service')),
    );

    /**
     * Get coupon_id
     *
     * @return int
     */
    public function get_coupon_id()
    {
        return $this->coupon_id;
    }

    /**
     * Set coupon_id
     *
     * @param int $coupon_id
     * @return $this
     */
    public function set_coupon_id($coupon_id)
    {
        $this->coupon_id = $coupon_id;

        return $this;
    }

    /**
     * Get service_id
     *
     * @return int
     */
    public function get_service_id()
    {
        return $this->service_id;
    }

    /**
     * Set service_id
     *
     * @param int $service_id
     * @return $this
     */
    public function set_service_id($service_id)
    {
        $this->service_id = $service_id;

        return $this;
    }

}
