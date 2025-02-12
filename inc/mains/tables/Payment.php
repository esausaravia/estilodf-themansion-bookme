<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class Payment
 */
class Payment extends Inc\Core\Table
{
    // status
    const STATUS_COMPLETED = 'completed';
    const STATUS_PENDING = 'pending';

    // payment type
    const PAY_DEPOSIT = 'deposit';
    const PAY_IN_FULL = 'in_full';

    // payment methods
    const TYPE_LOCAL = 'local';
    const TYPE_COUPON = 'coupon';  // when 100% coupon used
    const TYPE_PAYPAL = 'paypal';
    const TYPE_STRIPE = 'stripe';
    const TYPE_RAZORPAY = 'razorpay';
    const TYPE_AUTHORIZENET = 'authorize_net';
    const TYPE_2CHECKOUT = '2checkout';
    const TYPE_MOLLIE = 'mollie';
    const TYPE_WOOCOMMERCE = 'woocommerce';

    /** @var  string */
    protected $type;
    /** @var  float */
    protected $total;
    /** @var  float */
    protected $paid;
    /** @var  string */
    protected $paid_type = self::PAY_IN_FULL;
    /** @var  string */
    protected $status = self::STATUS_COMPLETED;
    /** @var  string */
    protected $details;
    /** @var  string */
    protected $created;

    protected static $table = 'bm_payments';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'type' => array('format' => '%s'),
        'total' => array('format' => '%f'),
        'paid' => array('format' => '%f'),
        'paid_type' => array('format' => '%s'),
        'status' => array('format' => '%s'),
        'details' => array('format' => '%s'),
        'created' => array('format' => '%s'),
    );

    /**
     * Get details
     *
     * @return string
     */
    public function get_details()
    {
        return $this->details;
    }

    /**
     * @param Inc\Mains\Booking\DataHolders\Order $order
     * @param Coupon|null $coupon
     * @return $this
     */
    public function set_details(Inc\Mains\Booking\DataHolders\Order $order, $coupon = null)
    {
        $details = array('items' => array(), 'coupon' => null, 'customer' => $order->get_customer()->get_full_name());

        foreach ($order->get_services() as $service) {
            if ($service->get_cb()->get_payment_id() != $this->get_id()) {
                // Skip items not related to this payment (e.g. series items with no associated payment).
                continue;
            }

            $details['items'][] = array(
                'ca_id' => $service->get_cb()->get_id(),
                'booking_date' => $service->get_booking()->get_start_date(),
                'service_name' => $service->get_service()->get_title(),
                'service_price' => $service->get_price(),
                'number_of_persons' => $service->get_cb()->get_number_of_persons(),
                'staff_name' => $service->get_staff()->get_full_name(),
            );
        }

        if ($coupon instanceof Coupon) {
            $details['coupon'] = array(
                'code' => $coupon->get_code(),
                'discount' => $coupon->get_discount(),
                'deduction' => $coupon->get_deduction(),
            );
        }

        $this->details = json_encode($details);

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function get_type()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function set_type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function get_total()
    {
        return $this->total;
    }

    /**
     * Set total
     *
     * @param float $total
     * @return $this
     */
    public function set_total($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get paid
     *
     * @return float
     */
    public function get_paid()
    {
        return $this->paid;
    }

    /**
     * Set paid
     *
     * @param float $paid
     * @return $this
     */
    public function set_paid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid_type
     *
     * @return string
     */
    public function get_paid_type()
    {
        return $this->paid_type;
    }

    /**
     * Set paid_type
     *
     * @param string $paid_type
     * @return $this
     */
    public function set_paid_type($paid_type)
    {
        $this->paid_type = $paid_type;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function get_status()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function set_status($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get created
     *
     * @return string
     */
    public function get_created()
    {
        return $this->created;
    }

    /**
     * Set created
     *
     * @param string $created
     * @return $this
     */
    public function set_created($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get display name for given payment type.
     *
     * @param string $type
     * @return string
     */
    public static function type_to_string($type)
    {
        switch ($type) {
            case self::TYPE_PAYPAL:
                return 'PayPal';
            case self::TYPE_LOCAL:
                return esc_html__('Local', 'bookme');
            case self::TYPE_STRIPE:
                return 'Stripe';
            case self::TYPE_RAZORPAY:
                return 'Razorpay';
            case self::TYPE_AUTHORIZENET:
                return 'Authorize.Net';
            case self::TYPE_2CHECKOUT:
                return '2Checkout';
            case self::TYPE_MOLLIE:
                return 'Mollie';
            case self::TYPE_COUPON:
                return esc_html__('Coupon', 'bookme');
            case self::TYPE_WOOCOMMERCE:
                return 'WooCommerce';
            default:
                return '';
        }
    }

    /**
     * Get status of payment.
     *
     * @param string $status
     * @return string
     */
    public static function status_to_string($status)
    {
        switch ($status) {
            case self::STATUS_COMPLETED:
                return esc_html__('Completed', 'bookme');
            case self::STATUS_PENDING:
                return esc_html__('Pending', 'bookme');
            default:
                return '';
        }
    }
}