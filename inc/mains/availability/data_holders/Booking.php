<?php

namespace Bookme\Inc\Mains\Availability\DataHolders;

use Bookme\Inc\Mains\Availability;

/**
 * Class Booking
 */
class Booking
{
    /** @var int */
    protected $service_id;
    /** @var int */
    protected $nop;
    /** @var Availability\TimeSlot */
    protected $time_slot;
    /** @var Availability\TimeSlot */
    protected $time_slot_with_padding;
    /** @var bool */
    protected $from_google;

    /**
     * Constructor.
     *
     * @param int $service_id
     * @param int $nop
     * @param string $start Format Y-m-d H:i[:s]
     * @param string $end Format Y-m-d H:i[:s]
     * @param int $padding_left
     * @param int $padding_right
     * @param bool $from_google
     */
    public function __construct($service_id, $nop, $start, $end, $padding_left, $padding_right, $from_google)
    {
        $this->service_id = (int)$service_id;
        $this->nop = (int)$nop;
        $this->time_slot = Availability\TimeSlot::from_dates($start, $end);
        $this->time_slot_with_padding = $this->time_slot->transform(-(int)$padding_left, (int)$padding_right);
        $this->from_google = (bool)$from_google;
    }

    /**
     * Get service ID.
     *
     * @return int
     */
    public function get_service_id()
    {
        return $this->service_id;
    }

    /**
     * Get number of persons.
     *
     * @return int
     */
    public function get_nop()
    {
        return $this->nop;
    }

    /**
     * Increase number of persons by given value.
     *
     * @param int $value
     * @return static
     */
    public function inc_nop($value)
    {
        $this->nop += $value;

        return $this;
    }

    /**
     * Get time_slot
     *
     * @return Availability\TimeSlot
     */
    public function get_time_slot()
    {
        return $this->time_slot;
    }

    /**
     * Get time_slot with padding.
     *
     * @return Availability\TimeSlot
     */
    public function get_time_slot_with_padding()
    {
        return $this->time_slot_with_padding;
    }

    /**
     * Check if it is from GC.
     *
     * @return bool
     */
    public function is_from_google()
    {
        return $this->from_google;
    }
}