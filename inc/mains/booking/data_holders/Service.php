<?php
namespace Bookme\Inc\Mains\Booking\DataHolders;

use Bookme\Inc\Mains\Tables;

/**
 * Class Service
 */
class Service
{
    /** @var Tables\Service */
    protected $service;
    /** @var Tables\Employee */
    protected $staff;
    /** @var Tables\Booking */
    protected $booking;
    /** @var Tables\CustomerBooking */
    protected $cb;
    /** @var Tables\EmployeeService */
    protected $staff_service;

    /**
     * Constructor.
     *
     * @param Tables\CustomerBooking $cb
     */
    public function __construct( Tables\CustomerBooking $cb )
    {
        $this->cb   = $cb;
    }

    /**
     * Set service.
     *
     * @param Tables\Service $service
     * @return $this
     */
    public function set_service(Tables\Service $service )
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service.
     *
     * @return Tables\Service
     */
    public function get_service()
    {
        if ( ! $this->service ) {
            $this->service = Tables\Service::find( $this->get_booking()->get_service_id() );
        }

        return $this->service;
    }

    /**
     * Set staff.
     *
     * @param Tables\Employee $staff
     * @return $this
     */
    public function set_staff(Tables\Employee $staff )
    {
        $this->staff = $staff;

        return $this;
    }

    /**
     * Get staff.
     *
     * @return Tables\Employee
     */
    public function get_staff()
    {
        if ( ! $this->staff ) {
            $this->staff = Tables\Employee::find( $this->get_booking()->get_staff_id() );
        }

        return $this->staff;
    }

    /**
     * Set booking.
     *
     * @param Tables\Booking $booking
     * @return $this
     */
    public function set_booking(Tables\Booking $booking )
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking.
     *
     * @return Tables\Booking
     */
    public function get_booking()
    {
        if ( ! $this->booking ) {
            $this->booking = Tables\Booking::find( $this->cb->get_booking_id() );
        }

        return $this->booking;
    }

    /**
     * Get customer booking.
     *
     * @return Tables\CustomerBooking
     */
    public function get_cb()
    {
        return $this->cb;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function get_price()
    {
        if ( ! $this->staff_service ) {
            $this->staff_service = new Tables\EmployeeService();
            $this->staff_service->load_by( array( 'staff_id' => $this->get_staff()->get_id(), 'service_id' => $this->get_service()->get_id() ) );
        }
        // Service price.
        return (float) $this->staff_service->get_price();

    }

    /**
     * Create new item.
     *
     * @param Tables\CustomerBooking $cb
     * @return static
     */
    public static function create( Tables\CustomerBooking $cb )
    {
        return new static( $cb );
    }
}