<?php
namespace Bookme\Inc\Mains\Availability\DataHolders;

use Bookme\Inc\Mains\Availability;
use Bookme\Inc\Mains\Functions\Date;
/**
 * Class Schedule
 */
class Schedule
{
    /** @var Availability\TimeSlotGroup[] */
    protected $days = array();
    /** @var array */
    protected $holidays = array();

    /**
     * Computes intersection between two schedules.
     *
     * @param self $schedule
     * @return static
     */
    public function intersect( self $schedule )
    {
        $new_schedule = new static();

        // Weekdays.
        foreach ( $this->days as $day_of_week => $day ) {
            if ( $schedule->has_day( $day_of_week ) ) {
                $new_day = new Availability\TimeSlotGroup();
                foreach ( $schedule->days[ $day_of_week ]->all() as $time_slot ) {
                    $new_day = $new_day->merge( $day->intersect( $time_slot ) );
                }
                $new_schedule->days[ $day_of_week ] = $new_day;
            }
        }

        // Holidays.
        $new_schedule->holidays = array_merge( $this->holidays, $schedule->holidays );

        return $new_schedule;
    }

    /**
     * Get schedule for given date (the date must not be day off).
     *
     * @param Date $dp
     * @param int $service_id
     * @param int $staff_id
     * @param Availability\TimeSlot $time_slot_limit
     * @return Availability\TimeSlotGroup
     */
    public function get_time_slots(Date $dp, $service_id, $staff_id, Availability\TimeSlot $time_slot_limit )
    {
        // Return weekday schedule.
        $collection = $this->days[ $dp->format( 'w' ) ];

        $time_slot_data = new TimeSlotData( $service_id, $staff_id );
        return $collection
            // Limit to requested time time_slot.
            //->intersect( $time_slot_limit )
            // Convert to date time_slots.
            ->map( function ( Availability\TimeSlot $time_slot ) use ( $dp, $time_slot_data ) {
                return new Availability\TimeSlot(
                    $dp->modify( $time_slot->start()->value() ),
                    $dp->modify( $time_slot->end()->value() ),
                    $time_slot_data
                );
            } );
    }

    /**
     * Create all day time_slot (no staff hours or breaks are taken in account).
     *
     * @param Date $dp
     * @param int $service_id
     * @param int $staff_id
     * @return Availability\TimeSlotGroup
     */
    public function get_all_day_time_slot(Date $dp, $service_id, $staff_id )
    {
        $collection = new Availability\TimeSlotGroup();

        return $collection->push( new Availability\TimeSlot( $dp, $dp->modify( '+1 day' ), new TimeSlotData( $service_id, $staff_id ) ) );
    }

    /**
     * Check if given date is day off.
     *
     * @param Date $dp
     * @return bool
     */
    public function is_day_off(Date $dp )
    {
        $date_Ymd = $dp->format( 'Y-m-d' );
        // Check for weekday.
        if ( isset ( $this->days[ $dp->format( 'w' ) ] ) ) {
            // Check for holiday.
            if ( ! isset ( $this->holidays[ $date_Ymd ] ) ) {
                // Check for repeating holiday.
                $date_md = $dp->format( 'm-d' );
                if ( ! isset ( $this->holidays[ $date_md ] ) ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Add schedule for a day of the week.
     *
     * @param int $day_of_week  0(Sun)-6(Sat)
     * @param string $start  Format H:i[:s]
     * @param string $end    Format H:i[:s]
     * @return $this
     */
    public function add_day($day_of_week, $start, $end )
    {
        $collection = new Availability\TimeSlotGroup();

        $this->days[ $day_of_week ] = $collection->push( Availability\TimeSlot::from_times( $start, $end ) );

        return $this;
    }

    /**
     * Check whether specific weekday has been set.
     *
     * @param int $day_of_week
     * @return bool
     */
    public function has_day($day_of_week )
    {
        return isset ( $this->days[ $day_of_week ] );
    }

    /**
     * Add break.
     *
     * @param integer $day_of_week  0(Sun)-6(Sat)
     * @param string $start  Format H:i[:s]
     * @param string $end    Format H:i[:s]
     * @return $this
     */
    public function add_break($day_of_week, $start, $end )
    {
        $this->days[ $day_of_week ] = $this->days[ $day_of_week ]->subtract( Availability\TimeSlot::from_times( $start, $end ) );

        return $this;
    }

    /**
     * Add holiday.
     *
     * @param string $date  Format Y-m[-d]
     * @return $this
     */
    public function add_holiday($date )
    {
        $this->holidays[ $date ] = true;

        return $this;
    }
}