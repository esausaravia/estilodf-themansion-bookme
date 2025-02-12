<?php
namespace Bookme\Inc\Mains\Booking;

use Bookme\Inc;
/**
 * Class SequenceItem
 */
class SequenceItem
{
    private $data = array(
        'service_id'        => null,
        'staff_ids'         => array(),
        'number_of_persons' => null,
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
     * @param mixed  $value
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
     * Get sub services with spare time.
     *
     * @return Inc\Mains\Tables\Service[]
     */
    public function get_services()
    {
        $result  = array();
        $service = $this->get_service();
        $result[] = $service;

        return $result;
    }

    /**
     * Get staff ids for sub service.
     *
     * @param Inc\Mains\Tables\Service $service
     * @return array
     */
    public function get_staff_ids_for_service(Inc\Mains\Tables\Service $service )
    {
        $staff_ids = array();
        $services = $this->get_services();
        if ( $service->get_id() == $services[0]->get_id() ) {
            $staff_ids = $this->get( 'staff_ids' );
        } else {
            global $wpdb;
            $res = $wpdb->get_results(
                $wpdb->prepare(
                    " SELECT staff_id FROM `".Inc\Mains\Tables\EmployeeService::get_table_name()."` 
                WHERE `service_id` = %d",
                    $service->get_id()
                ),
                ARRAY_A
            );
            foreach ( $res as $item ) {
                $staff_ids[] = $item['staff_id'];
            }
        }

        return $staff_ids;
    }
}