<?php
namespace Bookme\Inc\Mains\Booking;

use Bookme\Inc;
/**
 * Class CartItem
 */
class CartItem
{
    private $data = array(
        'service_id'        => null,
        'staff_ids'         => null,
        'number_of_persons' => null,
        'date'              => null,
        'days'              => null,
        'time_from'         => null,
        'time_to'           => null,
        'slots'             => null,
        'custom_fields'     => array()
    );

    /**
     * Constructor.
     */
    public function __construct() { }

    /**
     * Get data parameter.
     *
     * @param string $name
     * @return mixed
     */
    public function get( $name )
    {
        if ( array_key_exists( $name, $this->data ) ) {
            return $this->data[ $name ];
        }

        return false;
    }

    /**
     * Set data parameter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set( $name, $value )
    {
        $this->data[ $name ] = $value;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }

    /**
     * Set data.
     *
     * @param array $data
     */
    public function set_data(array $data )
    {
        foreach ( $data as $name => $value ) {
            $this->set( $name, $value );
        }
    }

    /**
     * Get service.
     *
     * @return Inc\Mains\Tables\Service
     */
    public function get_service()
    {
        return Inc\Mains\Tables\Service::find( $this->data['service_id'] );
    }

    /**
     * Get service price.
     *
     * @return double
     */
    public function get_service_price()
    {
        static $service_prices_cache = array();
        $slots   = $this->get( 'slots' );
        list ( $service_id, $staff_id ) = $slots[0];

        $service_start = 'unused'; //the price is the same for all services in day

        if ( isset ( $service_prices_cache[ $staff_id ][ $service_id ][ $service_start ] ) ) {
            $service_price = $service_prices_cache[ $staff_id ][ $service_id ][ $service_start ];
        } else {
            $staff_service = new Inc\Mains\Tables\EmployeeService();
            $staff_service->load_by( compact( 'staff_id', 'service_id' ) );
            $service_price = $staff_service->get_price();

            $service_prices_cache[ $staff_id ][ $service_id ][ $service_start ] = $service_price;
        }

        return $service_price;
    }

    /**
     * Get staff.
     *
     * @return Inc\Mains\Tables\Employee
     */
    public function get_staff()
    {
        $slots    = $this->get( 'slots' );
        $staff_id = $slots[0][1];

        return Inc\Mains\Tables\Employee::find( $staff_id );
    }
}