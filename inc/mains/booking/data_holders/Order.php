<?php
namespace Bookme\Inc\Mains\Booking\DataHolders;

use Bookme\Inc;
use Bookme\Inc\Mains\Tables;

/**
 * Class Order
 */
class Order
{
    /** @var Tables\Customer */
    protected $customer;
    /** @var Tables\Payment */
    protected $payment;
    /** @var Service[] */
    protected $services = array();

    /**
     * Constructor.
     *
     * @param Tables\Customer $customer
     */
    public function __construct( Tables\Customer $customer )
    {
        $this->customer = $customer;
    }

    /**
     * Get customer.
     *
     * @return Tables\Customer
     */
    public function get_customer()
    {
        return $this->customer;
    }

    /**
     * Set payment.
     *
     * @param Tables\Payment $payment
     * @return $this
     */
    public function set_payment(Tables\Payment $payment )
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Check if payment exists.
     *
     * @return bool
     */
    public function has_payment()
    {
        return (bool) $this->payment;
    }

    /**
     * Get payment.
     *
     * @return Tables\Payment
     */
    public function get_payment()
    {
        return $this->payment;
    }

    /**
     * Add item.
     *
     * @param string $id
     * @param Service $service
     * @return $this
     */
    public function add_service($id, Service $service )
    {
        $this->services[ $id ] = $service;

        return $this;
    }

    /**
     * Check if item exists.
     *
     * @param string $id
     * @return bool
     */
    public function has_service($id )
    {
        return isset ( $this->services[ $id ] );
    }

    /**
     * Get item.
     *
     * @param string $id
     * @return Service
     */
    public function get_service($id )
    {
        return $this->services[ $id ];
    }

    /**
     * Get items.
     *
     * @return Service[]
     */
    public function get_services()
    {
        return $this->services;
    }

    /**
     * Get flat array of items.
     *
     * @return Service[]
     */
    public function get_flat_services()
    {
        $result = array();
        foreach ($this->services as $service ) {
             $result[] = $service;
        }

        return $result;
    }

    /**
     * Create new order.
     *
     * @param Tables\Customer $customer
     * @return static
     */
    public static function create( Tables\Customer $customer )
    {
        return new static( $customer );
    }

    /**
     * Create new order from service.
     *
     * @param Service $service
     * @return static
     */
    public static function create_from_service(Service $service )
    {
        $order = static::create( Tables\Customer::find( $service->get_cb()->get_customer_id() ) )->add_service( 0, $service );

        if ( $service->get_cb()->get_payment_id() ) {
            $order->set_payment( Tables\Payment::find( $service->get_cb()->get_payment_id() ) );
        }

        return $order;
    }

    /**
     * Create Order from payment.
     *
     * @param Tables\Payment $payment
     * @return Order|null
     */
    public static function create_from_payment(Tables\Payment $payment )
    {
        global $wpdb;
        /** @var Tables\CustomerBooking[] $ca_list */
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `".Tables\CustomerBooking::get_table_name()."` WHERE `payment_id` = %d",
                $payment->get_id()
            ),
            ARRAY_A
        );
        $ca_list = Inc\Mains\Functions\System::bind_data_with_table( Tables\CustomerBooking::class, $data);
        if ( $ca_list ) {
            $customer = Tables\Customer::find( $ca_list[0]->get_customer_id() );
            $order    = static::create( $customer );
            $order->set_payment( $payment );
            foreach ( $ca_list as $i => $customer_booking ) {
                $series      = null;
                $compound    = null;
                /** @var Tables\Booking $booking */
                $booking = Tables\Booking::find( $customer_booking->get_booking_id() );
                $service  = Tables\Service::find( $booking->get_service_id() );

                $service = Service::create( $customer_booking )
                    ->set_service( $service )
                    ->set_booking( $booking );

                $order->add_service( $i, $service );
            }
            return $order;
        }
        return null;
    }
}