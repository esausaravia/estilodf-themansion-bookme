<?php
namespace Bookme\Inc\Mains\Availability\DataHolders;

use Bookme\Inc\Mains\Availability;
use Bookme\Inc\Mains\Tables;

/**
 * Class Staff
 */
class Staff
{
    /** @var Schedule */
    protected $schedule;
    /** @var Booking[] */
    protected $bookings;
    /** @var Service[] */
    protected $services;
    /** @var array */
    protected $workload;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->schedule = new Schedule();
        $this->bookings = array();
        $this->services = array();
    }

    /**
     * Get schedule.
     *
     * @return Schedule
     */
    public function get_schedule()
    {
        return $this->schedule;
    }

    /**
     * Add booking.
     *
     * @param Booking $booking
     * @return $this
     */
    public function add_booking(Booking $booking )
    {
        $date = $booking->get_time_slot()->start()->value()->format( 'Y-m-d' );
        if ( ! isset( $this->workload[ $date ] ) ) {
            $this->workload[ $date ] = 0;
        }
        $this->bookings[] = $booking;
        $this->workload[ $date ] += $booking->get_time_slot_with_padding()->length();

        return $this;
    }

    /**
     * Get bookings.
     *
     * @return Booking[]
     */
    public function get_bookings()
    {
        return $this->bookings;
    }

    /**
     * Add service.
     *
     * @param int    $service_id
     * @param double $price
     * @param int    $capacity_min
     * @param int    $capacity_max
     * @param string $staff_preference_rule
     * @param int    $staff_preference_order
     * @return $this
     */
    public function add_service($service_id, $price, $capacity_min, $capacity_max, $staff_preference_rule, $staff_preference_order )
    {
        $this->services[ $service_id ] = new Service( $price, $capacity_min, $capacity_max, $staff_preference_rule, $staff_preference_order );

        return $this;
    }

    /**
     * Tells whether staff provides given service.
     *
     * @param int $service_id
     * @return bool
     */
    public function provides_service($service_id )
    {
        return isset ( $this->services[ $service_id ] );
    }

    /**
     * Get service by ID.
     *
     * @param int $service_id
     * @return Service
     */
    public function get_service($service_id )
    {
        return $this->services[ $service_id ];
    }

    /**
     * @param $date
     * @return int
     */
    public function get_workload($date )
    {
        if ( isset( $this->workload[ $date ] ) ) {
            return $this->workload[ $date ];
        }

        return 0;
    }

    /**
     * @param Staff $staff
     * @param Availability\TimeSlot $slot
     * @return bool
     */
    public function more_preferable_than(Staff $staff, Availability\TimeSlot $slot )
    {
        $service_id = $slot->service_id();
        $service    = $this->get_service( $service_id );

        switch ( $service->get_employee_preference_rule() ) {
            case Tables\Service::PREFERRED_ORDER:
                return $service->get_employee_preference_order() < $staff->get_service( $service_id )->get_employee_preference_order();
            case Tables\Service::PREFERRED_LEAST_OCCUPIED:
                $date  = $slot->start()->value()->format( 'Y-m-d' );
                return $this->get_workload( $date ) < $staff->get_workload( $date );
            case Tables\Service::PREFERRED_MOST_OCCUPIED:
                $date  = $slot->start()->value()->format( 'Y-m-d' );
                return $this->get_workload( $date ) > $staff->get_workload( $date );
            case Tables\Service::PREFERRED_LEAST_EXPENSIVE:
                return $service->price() < $staff->get_service( $service_id )->price();
            case Tables\Service::PREFERRED_MOST_EXPENSIVE:
            default:
                return $service->price() > $staff->get_service( $service_id )->price();
        }
    }
}