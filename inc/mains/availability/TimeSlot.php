<?php

namespace Bookme\Inc\Mains\Availability;

use Bookme\Inc\Mains\Functions\Date;
use Bookme\Inc\Mains\Functions\Time;

/**
 * Class TimeSlot
 */
class TimeSlot
{
    const AVAILABLE = 1;
    const PARTIALLY_BOOKED = 2;
    const FULLY_BOOKED = 3;

    /** @var Date|Time */
    protected $start;

    /** @var Date|Time */
    protected $end;

    /** @var DataHolders\TimeSlotData */
    protected $data;

    /**
     * Constructor.
     *
     * @param Date|Time $start
     * @param Date|Time $end
     * @param DataHolders\TimeSlotData $data
     */
    public function __construct($start, $end, DataHolders\TimeSlotData $data = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->data = $data;
    }

    /**
     * Create TimeSlot object from date strings.
     *
     * @param string $start Format Y-m-d H:i[:s]
     * @param string $end Format Y-m-d H:i[:s]
     * @param DataHolders\TimeSlotData $data
     * @return static
     */
    public static function from_dates($start, $end, DataHolders\TimeSlotData $data = null)
    {
        return new static(Date::from_string($start), Date::from_string($end), $data);
    }

    /**
     * Create TimeSlot object from time strings.
     *
     * @param string $start Format H:i[:s]
     * @param string $end Format H:i[:s]
     * @param DataHolders\TimeSlotData $data
     * @return static
     */
    public static function from_times($start, $end, DataHolders\TimeSlotData $data = null)
    {
        return new static(Time::from_string($start), Time::from_string($end), $data);
    }

    /**
     * Get time_slot start.
     *
     * @return Date|Time
     */
    public function start()
    {
        return $this->start;
    }

    /**
     * Ger time_slot end.
     *
     * @return Date|Time
     */
    public function end()
    {
        return $this->end;
    }

    /**
     * Get time_slot data.
     *
     * @return DataHolders\TimeSlotData
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Get time_slot length.
     *
     * @return int
     */
    public function length()
    {
        return $this->end->diff($this->start);
    }

    /**
     * Tells whether time_slot is valid (start point is less then end point).
     *
     * @return bool
     */
    public function valid()
    {
        return $this->start->lte($this->end);
    }

    /**
     * Tells whether time_slot contains specific point.
     *
     * @param Date|Time $point
     * @return bool
     */
    public function contains($point)
    {
        return $this->start->lte($point) && $this->end->gte($point);
    }

    /**
     * Tells whether two time_slots are equal.
     *
     * @param self $time_slot
     * @return bool
     */
    public function equals(self $time_slot)
    {
        return $this->start->eq($time_slot->start()) && $this->end->eq($time_slot->end());
    }

    /**
     * Tells whether two time_slots overlap.
     *
     * @param self $time_slot
     * @return bool
     */
    public function overlaps(self $time_slot)
    {
        return $this->start->lt($time_slot->end()) && $this->end->gt($time_slot->start());
    }

    /**
     * Tells whether time_slot contains all points of another time_slot.
     *
     * @param TimeSlot $time_slot
     * @return bool
     */
    public function wraps(self $time_slot)
    {
        return $this->start->lte($time_slot->start()) && $this->end->gte($time_slot->end());
    }

    /**
     * Computes the intersection between two time_slots.
     *
     * @param self $time_slot
     * @return static|null
     */
    public function intersect(self $time_slot)
    {
        return $this->overlaps($time_slot)
            ? new static(self::max($this->start, $time_slot->start()), self::min($this->end, $time_slot->end()), $this->data)
            : null;
    }

    /**
     * Computes the result of subtraction of two time_slots.
     *
     * @param self $time_slot
     * @param self $removed
     * @return TimeSlotGroup
     */
    public function subtract(self $time_slot, self &$removed = null)
    {
        $collection = new TimeSlotGroup();

        $removed = $this->intersect($time_slot);

        if ($this->start->lt($time_slot->start())) {
            $collection->push(new static($this->start, self::min($this->end, $time_slot->start()), $this->data));
        }

        if ($time_slot->end()->lt($this->end)) {
            $collection->push(new static(self::max($this->start, $time_slot->end()), $this->end, $this->data));
        }

        return $collection;
    }

    /**
     * Split time_slot into smaller time_slots.
     *
     * @param mixed $length
     * @return TimeSlotGroup
     */
    public function split($length, $padding_right = 0)
    {
        $collection = new TimeSlotGroup();

        $frame = $this->resize($length);

        // add padding right
        $length += $padding_right;
        while ($time_slot = $this->intersect($frame)) {
            $collection->push($time_slot);
            $frame = $frame->transform($length, $length );
        };

        return $collection;
    }

    /**
     * Computes the result of modifying the edge points according to given values.
     *
     * @param mixed $modify_start
     * @param mixed $modify_end
     * @return static
     */
    public function transform($modify_start, $modify_end)
    {
        return new static($this->start->modify($modify_start), $this->end->modify($modify_end), $this->data);
    }

    /**
     * Computes the result of moving the end point to given length from the start point.
     *
     * @param mixed $length
     * @return static
     */
    public function resize($length)
    {
        return new static($this->start, $this->start->modify($length), $this->data);
    }

    /**
     * Create a copy of the time_slot with new data.
     *
     * @param DataHolders\TimeSlotData $new_data
     * @return TimeSlot
     */
    public function replace_data(DataHolders\TimeSlotData $new_data)
    {
        return new static($this->start, $this->end, $new_data);
    }

    /**
     * Get max point.
     *
     * @param Date|Time $x
     * @param Date|Time $y
     * @return Date|Time
     */
    private static function max($x, $y)
    {
        return $x->gte($y) ? $x : $y;
    }

    /**
     * Get min point.
     *
     * @param Date|Time $x
     * @param Date|Time $y
     * @return Date|Time
     */
    private static function min($x, $y)
    {
        return $x->lte($y) ? $x : $y;
    }

    /**
     * Get service ID.
     *
     * @return int
     */
    public function service_id()
    {
        return $this->data->service_id();
    }

    /**
     * Get staff ID.
     *
     * @return int
     */
    public function staff_id()
    {
        return $this->data->staff_id();
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function state()
    {
        return $this->data->state();
    }

    /**
     * Get next slot.
     *
     * @return static
     */
    public function next_slot()
    {
        return $this->data->next_slot();
    }

    /**
     * Create a copy of the data with new staff ID.
     *
     * @param int $new_staff_id
     * @return static
     */
    public function replace_staff_id($new_staff_id)
    {
        return $this->replace_data($this->data->replace_staff_id($new_staff_id));
    }

    /**
     * Create a copy of the data with new state.
     *
     * @param int $new_state
     * @return static
     */
    public function replace_state($new_state)
    {
        return $this->replace_data($this->data->replace_state($new_state));
    }

    /**
     * Create a copy of the data with new next slot.
     *
     * @param TimeSlot|null $new_next_slot
     * @return static
     */
    public function replace_next_slot($new_next_slot)
    {
        return $this->replace_data($this->data->replace_next_slot($new_next_slot));
    }

    /**
     * Tells whether time_slot's state is fully booked
     *
     * @return bool
     */
    public function fully_booked()
    {
        return $this->data->state() == self::FULLY_BOOKED;
    }

    /**
     * Tells whether time_slot's state is not fully booked.
     *
     * @return bool
     */
    public function not_fully_booked()
    {
        return $this->data->state() != self::FULLY_BOOKED;
    }

    /**
     * Build slot data.
     *
     * @return array
     */
    public function build_slot_data()
    {
        $result = array(array($this->service_id(), $this->staff_id(), $this->start->value()->format('Y-m-d H:i:s')));

        if ($this->data()->has_next_slot()) {
            $result = array_merge($result, $this->next_slot()->build_slot_data());
        }

        return $result;
    }
}