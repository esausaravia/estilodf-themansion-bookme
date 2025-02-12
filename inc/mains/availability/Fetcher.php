<?php
namespace Bookme\Inc\Mains\Availability;

use Bookme\Inc\Mains\Functions\Date;
use Bookme\Inc\Mains\Functions\Time;

/**
 * Class Fetcher
 */
class Fetcher implements \Iterator
{
    /** @var int */
    protected $srv_id;
    /** @var int */
    protected $srv_duration;
    /** @var int */
    protected $srv_duration_days;
    /** @var int */
    protected $srv_padding_left;
    /** @var int */
    protected $srv_padding_right;
    /** @var DataHolders\Staff[] */
    protected $staff_members;
    /** @var DataHolders\Schedule[] */
    protected $staff_schedule;
    /** @var int */
    protected $slot_length;
    /** @var Date */
    protected $dp;
    /** @var int */
    protected $nop;
    /** @var TimeSlot */
    protected $time_limit;
    /** @var int */
    protected $spare_time;
    /** @var static */
    protected $next_fetcher;
    /** @var TimeSlotGroup */
    protected $next_slots;
    /** @var TimeSlotGroup */
    protected $past_slots;

    /**
     * Constructor
     * @param array $data
     */
    public function __construct($data)
    {
        $staff_members = $data['staff'];
        $service_schedule = $data['service_schedule'];
        $service_id = $data['service']->get_id();
        $service_duration = $data['service']->get_duration();
        $service_padding_left = $data['service']->get_padding_left();
        $service_padding_right = $data['service']->get_padding_right();

        $this->staff_members = array();
        $this->staff_schedule = array();
        $this->srv_id = (int)$data['service']->get_id();
        $this->srv_duration = (int)min($service_duration, DAY_IN_SECONDS);
        $this->srv_duration_days = (int)($service_duration / DAY_IN_SECONDS);
        $this->srv_padding_left = (int)$service_padding_left;
        $this->srv_padding_right = (int)$service_padding_right;
        $this->dp = $data['start_date']->modify('today');
        $this->slot_length = (int)($this->srv_duration_days ? DAY_IN_SECONDS : min($data['slot_length'], DAY_IN_SECONDS));
        $this->nop = (int)$data['nop'];
        $this->time_limit = TimeSlot::from_times($data['time_from'], $data['time_to']);
        $this->spare_time = (int)$data['spare_time'];
        $this->next_fetcher = $data['next'];

        // filter staff members who provide the service with the capacity
        /* @var \Bookme\Inc\Mains\Availability\DataHolders\Staff $staff */
        foreach ($staff_members as $staff_id => $staff) {
            if ($staff->provides_service($this->srv_id)) {
                $service = $staff->get_service($this->srv_id);
                if ($service->capacity_max() >= $this->nop && $service->capacity_min() <= $this->nop) {
                    $this->staff_members[$staff_id] = $staff;
                    // Prepare staff schedule
                    $schedule = $staff->get_schedule();
                    if (isset ($service_schedule[$service_id])) {
                        $schedule = $schedule->intersect($service_schedule[$service_id]);
                    }
                    $this->staff_schedule[$staff_id] = $schedule;
                }
            }
        }

        // next generator
        if ($this->next_fetcher) {
            $this->next_slots = new TimeSlotGroup();
            $this->next_fetcher->rewind();
        }

        if ($this->srv_duration_days > 1) {
            $this->past_slots = new TimeSlotGroup();
        }
    }

    /**
     * Get service duration in days
     *
     * @return int
     */
    public function service_duration_in_days()
    {
        return $this->srv_duration_days;
    }

    /**
     * @inheritdoc
     * @return TimeSlotGroup
     */
    public function current()
    {
        $result = new TimeSlotGroup();

        foreach ($this->staff_members as $staff_id => $staff) {
            $schedule = $this->staff_schedule[$staff_id];
            if (!$schedule->is_day_off($this->dp)) {
                // Create time_slots from the schedule
                $time_slots = $this->srv_duration_days
                    ? $schedule->get_all_day_time_slot($this->dp, $this->srv_id, $staff_id)
                    : $schedule->get_time_slots($this->dp, $this->srv_id, $staff_id, $this->time_limit);

                // Create time_slots from staff bookings
                $max_capacity = $staff->get_service($this->srv_id)->capacity_max();

                foreach ($staff->get_bookings() as $booking) {
                    $time_slot_to_remove = $booking->get_time_slot_with_padding()->transform(-$this->srv_padding_right, $this->srv_padding_left);
                    $new_time_slots = new TimeSlotGroup();
                    $removed = new TimeSlotGroup();
                    foreach ($time_slots->all() as $r) {
                        if ($r->overlaps($time_slot_to_remove)) {
                            $new_time_slots = $new_time_slots->merge($r->subtract(
                                ($extra_length = $time_slot_to_remove->end()->diff($r->start()) % $this->slot_length)
                                    ? $time_slot_to_remove->transform(null, $this->slot_length - $extra_length)
                                    : $time_slot_to_remove,
                                $removed_time_slot
                            ));
                            /** @var TimeSlot $removed_time_slot */
                            if ($removed_time_slot) {
                                $removed->push($removed_time_slot);
                            }
                        } else {
                            $new_time_slots->push($r);
                        }
                    }
                    $time_slots = $new_time_slots;
                    // Reset time_slots
                    if ($removed->is_not_empty()) {
                        $data = $removed->get(0)->data()->replace_state(TimeSlot::FULLY_BOOKED);
                        foreach ($removed->all() as $time_slot) {
                            $time_slots->push($time_slot->replace_data($data));
                        }

                        // handle partially booked time_slots
                        if ($booking->get_service_id() == $this->srv_id && $booking->get_nop() <= $max_capacity - $this->nop) {
                            $booking_time_slot = $booking->get_time_slot();
                            foreach ($removed->all() as $time_slot) {
                                if ($time_slot->contains($booking_time_slot->start())) {
                                    $time_slots->push(
                                        $booking_time_slot
                                            ->resize($this->slot_length)
                                            ->replace_data($time_slot->data()->replace_state(TimeSlot::PARTIALLY_BOOKED))
                                    );
                                    break;
                                }
                            }
                        }
                    }
                }

                // Get Slots
                foreach ($time_slots->all() as $time_slot) {
                    if ($time_slot->state() == TimeSlot::AVAILABLE) {
                        // Shorten time_slot by service duration
                        $time_slot = $time_slot->transform(null, -$this->srv_duration);
                        if (!$time_slot->valid()) {
                            // If time_slot is not valid skip it
                            continue;
                        }
                        // Enlarge time_slot by slot length
                        $time_slot = $time_slot->transform(null, $this->slot_length);
                    }
                    // Split time_slot into slots.
                    foreach ($time_slot->split($this->slot_length, $this->srv_padding_right)->all() as $slot) {
                        if ($slot->length() < $this->slot_length) {
                            // Skip slots with not enough length.
                            continue;
                        }
                        $timestamp = $slot->start()->value()->getTimestamp();
                        if ($result->has($timestamp)) {
                            // If slot already has this timestamp
                            if ($slot->fully_booked()) {
                                continue;
                            } else {
                                $ex_slot = $result->get($timestamp);
                                if ($ex_slot->not_fully_booked()) {
                                    $ex_staff = $this->staff_members[$ex_slot->staff_id()];
                                    if ($staff->more_preferable_than($ex_staff, $ex_slot)) {
                                        // Replace staff ID in the existing slot if current staff is more preferable.
                                        $slot = $ex_slot->replace_staff_id($staff_id);
                                    } else {
                                        continue;
                                    }
                                }
                            }
                        }

                        if ($this->next_fetcher && !$slot->next_slot() && $slot->not_fully_booked()) {
                            if (($slot = $this->try_find_next_slot($slot)) == false) {
                                continue;
                            }
                        }

                        if ($this->srv_duration_days > 1 && $slot->state() == TimeSlot::AVAILABLE) {
                            if (($slot = $this->try_find_past_slot($timestamp, $slot)) == false) {
                                continue;
                            }
                        }
                        // Add slot to result.
                        $result->put($timestamp, $slot);
                    }
                }
            }
        }

        return $result->ksort();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        // Start one day earlier to cover night shifts.
        $this->dp = $this->dp->modify('-1 day');
    }

    /**
     * @inheritdoc
     * @return Date
     */
    public function key()
    {
        return $this->dp;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->dp = $this->dp->modify('+1 day');
    }

    /**
     * Infinite search.
     *
     * @return bool
     */
    public function valid()
    {
        return true;
    }

    /**
     * Try to find next slot for consecutive bookings
     *
     * @param TimeSlot $slot
     * @return TimeSlot|false
     */
    private function try_find_next_slot(TimeSlot $slot)
    {
        $next_start = $slot->start()->modify($this->srv_duration + $this->spare_time);
        $padding = $this->srv_padding_right + $this->next_fetcher->srv_padding_left;

        $next_slot = $this->find_next_slot($next_start);
        if (
            $next_slot == false ||
            $next_slot->fully_booked() ||
            $padding != 0 && $next_slot->staff_id() == $slot->staff_id()
        ) {
            $next_slot = $this->find_next_slot($next_start->modify($padding));
            if (
                $next_slot == false ||
                $next_slot->fully_booked() ||
                $next_slot->staff_id() != $slot->staff_id()
            ) {
                $next_slot = null;
            }
        }
        if ($next_slot) {
            // Connect slots with each other.
            $slot = $slot->replace_next_slot($next_slot);
        } else {
            // If no next slot was found then return false.
            return false;
        }

        return $slot;
    }

    /**
     * Try to find a valid slot in the past for multi-day services
     *
     * @param int $timestamp
     * @param TimeSlot $slot
     * @return TimeSlot|bool
     */
    private function try_find_past_slot(&$timestamp, TimeSlot $slot)
    {
        // Store slot for further reference.
        $this->past_slots->put($timestamp, $slot);
        // Check if there are enough valid days for service duration in the past.
        for ($d = 1; $d < $this->srv_duration_days; ++$d) {
            $timestamp -= DAY_IN_SECONDS;
            if (
                !$this->past_slots->has($timestamp) ||
                $this->past_slots->get($timestamp)->staff_id() != $slot->staff_id()
            ) {
                return false;
            }
        }
        // Replace slot with one from the day when service starts.
        $slot = $this->past_slots->get($timestamp)->replace_next_slot($slot->next_slot());

        return $slot;
    }

    /**
     * Find next slot for consecutive bookings
     *
     * @param Date|Time $start
     * @return TimeSlot|false
     */
    private function find_next_slot($start)
    {
        while (
            $this->next_fetcher->valid() &&
            // Do search only while next fetcher is producing slots earlier than the requested point
            $start->modify($this->next_fetcher->srv_duration_days . ' days')->gt($this->next_fetcher->key())
        ) {
            $this->next_slots = $this->next_slots->union($this->next_fetcher->current());
            $this->next_fetcher->next();
        }

        return $this->next_slots->get($start->value()->getTimestamp());
    }
}