<?php
namespace Bookme\Inc\Mains\Availability\DataHolders;

use Bookme\Inc\Mains\Availability;
/**
 * Class TimeSlotData
 */
class TimeSlotData
{
    /** @var int */
    protected $service_id;
    /** @var int */
    protected $staff_id;
    /** @var int */
    protected $state;
    /** @var Availability\TimeSlot */
    protected $next_slot;

    /**
     * Constructor.
     *
     * @param int $service_id
     * @param int $staff_id
     * @param int $state
     * @param Availability\TimeSlot|null $next_slot
     */
    public function __construct( $service_id, $staff_id, $state = Availability\TimeSlot::AVAILABLE, $next_slot = null )
    {
        $this->service_id = $service_id;
        $this->staff_id   = $staff_id;
        $this->state      = $state;
        $this->next_slot  = $next_slot;
    }

    /**
     * Get service ID.
     *
     * @return int
     */
    public function service_id()
    {
        return $this->service_id;
    }

    /**
     * Get staff ID.
     *
     * @return int
     */
    public function staff_id()
    {
        return $this->staff_id;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function state()
    {
        return $this->state;
    }

    /**
     * Get next slot.
     *
     * @return Availability\TimeSlot
     */
    public function next_slot()
    {
        return $this->next_slot;
    }

    /**
     * Check whether next slot is set.
     *
     * @return bool
     */
    public function has_next_slot()
    {
        return $this->next_slot != null;
    }

    /**
     * Create a copy of the data with new staff ID.
     *
     * @param int $new_staff_id
     * @return static
     */
    public function replace_staff_id($new_staff_id )
    {
        return new static( $this->service_id, $new_staff_id, $this->state, $this->next_slot );
    }

    /**
     * Create a copy of the data with new state.
     *
     * @param int $new_state
     * @return static
     */
    public function replace_state($new_state )
    {
        return new static( $this->service_id, $this->staff_id, $new_state, $this->next_slot );
    }

    /**
     * Create a copy of the data with new next slot.
     *
     * @param Availability\TimeSlot|null $new_next_slot
     * @return static
     */
    public function replace_next_slot($new_next_slot )
    {
        return new static( $this->service_id, $this->staff_id, $this->state, $new_next_slot );
    }
}