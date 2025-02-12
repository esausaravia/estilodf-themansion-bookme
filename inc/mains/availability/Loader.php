<?php
namespace Bookme\Inc\Mains\Availability;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions;

/**
 * Class Loader
 */
class Loader
{
    /** @var Inc\Mains\Booking\UserData */
    protected $user_data;
    /** @var array */
    protected $slots;
    /** @var int */
    protected $slot_length;
    /** @var bool */
    protected $srv_duration_as_slot_length;
    /** @var array|null */
    protected $last_fetched_slot = null;
    /** @var string|null */
    protected $selected_date = null;
    /** @var DataHolders\Staff[] */
    protected $staff = array();
    /** @var DataHolders\Schedule[] */
    protected $service_schedule = array();
    /** @var int */
    protected $srv_duration_days;
    /** @var callable */
    protected $callback_stop;
    /** @var Functions\Date */
    public $start_dp;
    /** @var Functions\Date */
    public $end_dp;
    /** @var Functions\Date */
    public $client_start_dp;
    /** @var Functions\Date */
    public $client_end_dp;
    /** @var \wpdb */
    private $wpdb;

    /**
     * Constructor
     *
     * @param Inc\Mains\Booking\UserData $user_data
     * @param callable $callback_stop
     */
    public function __construct(Inc\Mains\Booking\UserData $user_data, $callback_stop = null)
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->user_data = $user_data;
        $this->slot_length = Functions\System::get_time_slot_length();
        $this->srv_duration_as_slot_length = Functions\System::service_duration_as_slot_length();
        $this->callback_stop = $callback_stop;
    }

    /**
     * Init slots fetcher
     *
     * @return Fetcher
     */
    private function fetch()
    {
        $fetcher = null;

        /** @var Inc\Mains\Booking\SequenceItem $sequence_item */
        foreach (array_reverse($this->user_data->sequence->get_items()) as $sequence_item) {
            $services = $sequence_item->get_services();
            $spare_time = 0;
            foreach (array_reverse($services) as $key => $service) {
                if ($service instanceof Inc\Mains\Tables\Service) {
                    $data = array(
                        'staff' => $this->staff,
                        'service' => $service,
                        'service_schedule' => $this->service_schedule,
                        'slot_length' => $this->srv_duration_as_slot_length ? $service->get_duration() : $this->slot_length,
                        'nop' => $sequence_item->get('number_of_persons'),
                        'start_date' => $this->start_dp,
                        'time_from' => $this->user_data->get('time_from'),
                        'time_to' => $this->user_data->get('time_to'),
                        'spare_time' => $spare_time,
                        'next' => $fetcher
                    );
                    $fetcher = new Fetcher($data);
                    $spare_time = 0;
                }
            }
        }

        $this->srv_duration_days = $fetcher->service_duration_in_days();

        return $fetcher;
    }

    /**
     * Prepare dates and staff data
     *
     * @return $this
     */
    public function prepare()
    {
        $this->prepare_dates();
        $this->prepare_staff_data();

        return $this;
    }

    /**
     * Set selected date
     *
     * @param string $selected_date
     * @return $this
     */
    public function set_selected_date($selected_date)
    {
        $this->selected_date = $selected_date;

        return $this;
    }

    /**
     * @return array
     */
    public function get_slots()
    {
        return $this->slots;
    }

    /**
     * Check first service has duration in days
     *
     * @return bool
     */
    public function is_service_duration_in_days()
    {
        return $this->srv_duration_days >= 1;
    }

    /**
     * Load and init.
     *
     * @param callable $callback_break
     */
    public function load($callback_break = null)
    {
        $this->slots = array();

        // Prepare break callback.
        if (!is_callable($callback_break)) {
            $callback_break = array($this, 'break_default');
        }

        // Do search.
        $slots_count = 0;
        $do_break = false;
        $fetcher = $this->fetch();
        foreach ($fetcher as $slots) {
            $dp = $fetcher->key();
            // For empty slots check client end date here.
            if (call_user_func($callback_break, $dp, $this->srv_duration_days, $slots_count)) {
                break;
            }
            foreach ($slots->all() as $slot) {
                if ($do_break) {
                    break 2;
                }
                /** @var Functions\Date $client_dp */
                $client_dp = $slot->start()->to_client_tz();
                if ($client_dp->lt($this->client_start_dp)) {
                    // Skip slots earlier than requested time.
                    continue;
                }

                // Decide how to group slots
                $group = $this->group_default($client_dp);

                // Decide when to stop.
                if (!isset ($this->slots[$group])) {
                    $stop = is_callable($this->callback_stop)
                        ? call_user_func($this->callback_stop, $client_dp, count($this->slots), $slots_count)
                        : $this->stop_default($client_dp, count($this->slots), $slots_count);

                    switch ($stop) {
                        case 0:  // Continue search.
                            break;
                        case 1:  // Immediate stop.
                            break 3;
                        case 2:  // Check whether there are more slots and then stop.
                            $do_break = true;
                            continue 2;
                    }
                }

                if ($slot->not_fully_booked()) {
                    // Add slot to result.
                    $this->slots[$group][] = $slot;

                    ++$slots_count;
                }
            }

        }
    }

    /**
     * Find start and end dates.
     */
    private function prepare_dates()
    {
        // Initial constraints in WP time zone.
        $now = Functions\Date::now();
        $min_start = $now->modify(Functions\System::get_minimum_time_prior_booking());
        $max_end = $now->modify(Functions\System::get_maximum_available_days_for_booking() . ' days midnight');

        // Find start date.
        if ($this->last_fetched_slot) {
            // Set start date to the next day after last fetched slot.
            $this->client_start_dp = Functions\Date::from_string($this->last_fetched_slot[0][2])->to_client_tz()->modify('tomorrow');
        } else {
            // Requested date.
            $this->client_start_dp = Functions\Date::from_string_in_client_tz($this->selected_date ?: $this->user_data->get('date'));
            if ($this->client_start_dp->lt($min_start)) {
                $this->client_start_dp = $min_start->to_client_tz();
            }
        }

        // Find end date.
        $this->client_end_dp = $max_end->to_client_tz();

        // Start and end dates in WP time zone.
        $this->start_dp = $this->client_start_dp->to_wp_tz();
        $this->end_dp = $max_end;
    }

    /**
     * Prepare data for staff.
     */
    private function prepare_staff_data()
    {
        // Prepare staff IDs for each service.
        $ss_ids = array();
        foreach ($this->user_data->sequence->get_items() as $sequence_item) {
            $services = $sequence_item->get_services();
            foreach ($services as $service) {
                $staff_ids = $sequence_item->get_staff_ids_for_service($service);
                $service_id = $service->get_id();
                if (!isset ($ss_ids[$service_id])) {
                    $ss_ids[$service_id] = array();
                }
                $ss_ids[$service_id] = array_unique(array_merge($ss_ids[$service_id], $staff_ids));
            }
        }

        // Service price and capacity for each staff member.
        $where = array();
        foreach ($ss_ids as $service_id => $staff_ids) {
            $where[] = sprintf(
                'ss.service_id = %d AND ss.staff_id IN (%s)',
                $service_id,
                empty ($staff_ids) ? 'NULL' : implode(',', $staff_ids)
            );
        }

        // Service preference rule and Staff preference orders
        $sp_spo = array();
        $preference_orders = $this->wpdb->get_results(
                "SELECT 
                            s.staff_preference, s.id AS service_id, sp.staff_id, sp.position 
                        FROM `".Inc\Mains\Tables\Service::get_table_name()."` AS `s` 
                        LEFT JOIN `".Inc\Mains\Tables\EmployeePreferenceOrder::get_table_name()."` AS `sp` ON sp.service_id = s.id 
                        WHERE `s`.`id` IN (".implode(',', array_keys($ss_ids)) .") 
                        ORDER BY sp.service_id ASC"
            ,
            ARRAY_A
        );

        foreach ($preference_orders as $row) {
            if (!isset($sp_spo[$row['service_id']])) {
                $rule = $row['staff_preference'];
                $sp_spo[$row['service_id']] = array(
                    'rule' => $rule,
                    'order' => array()
                );
                if ($rule !== Inc\Mains\Tables\Service::PREFERRED_ORDER) {
                    break;
                }

            }
            $sp_spo[$row['service_id']]['order'][$row['staff_id']] = $row['position'];
        }

        $staff_services = $this->wpdb->get_results(
                "SELECT 
                            ss.service_id, ss.staff_id, ss.price, ss.capacity_min, ss.capacity_max 
                        FROM `".Inc\Mains\Tables\EmployeeService::get_table_name()."` AS `ss` 
                        WHERE ".implode(' OR ', $where)
            ,
            ARRAY_A
        );

        foreach ($staff_services as $staff_service) {
            $staff_id = $staff_service['staff_id'];
            $service_id = $staff_service['service_id'];
            if (!isset ($this->staff[$staff_id])) {
                $this->staff[$staff_id] = new DataHolders\Staff();
            }
            $staff_preference_rule = Inc\Mains\Tables\Service::PREFERRED_MOST_EXPENSIVE;
            $staff_preference_order = 0;

            if (isset($sp_spo[$service_id])) {
                $staff_preference_rule = $sp_spo[$service_id]['rule'];
                if (isset($sp_spo[$service_id]['order'][$staff_id])) {
                    $staff_preference_order = $sp_spo[$service_id]['order'][$staff_id];
                }
            }

            $this->staff[$staff_id]->add_service($service_id, $staff_service['price'], $staff_service['capacity_min'], $staff_service['capacity_max'], $staff_preference_rule, $staff_preference_order);
        }

        // Holidays.
        $this->handle_holidays();

        // Working schedule
        $this->handle_working_schedules();

        // Bookings
        $this->handle_bookings();

        // Cart bookings.
        $this->handle_cart_bookings();

        // Google Calendar events.
        $this->handle_google_calendar();
    }

    /**
     * Callback for making decision whether to break fetcher loop.
     *
     * @param Functions\Date $dp
     * @param int $srv_duration_days
     * @param int $slots_count
     * @return bool
     */
    private function break_default(Functions\Date $dp, $srv_duration_days, $slots_count)
    {
        return $dp->modify(-($srv_duration_days > 1 ? $srv_duration_days - 1 : 0) . ' days')->gte($this->client_end_dp);
    }

    /**
     * Callback for computing slot's group.
     *
     * @param Functions\Date $client_dp
     * @return string
     */
    private function group_default(Functions\Date $client_dp)
    {
        return $client_dp
            ->modify($this->srv_duration_days ? 'first day of this month' : null)
            ->format('Y-m-d');
    }

    /**
     * Callback for making decision whether to stop for default mode.
     *
     * @param Functions\Date $client_dp
     * @param int $groups_count
     * @param int $slots_count
     * @return int
     */
    private function stop_default(Functions\Date $client_dp, $groups_count, $slots_count)
    {
        return $groups_count >= 1 ? 2 : 0;
    }

    // Helper functions

    private function handle_working_schedules()
    {
        $working_schedule = $this->wpdb->get_results(
                "SELECT 
                            ssi.*, break.start_time AS break_start, break.end_time AS break_end 
                        FROM `".Inc\Mains\Tables\EmployeeSchedule::get_table_name()."` AS `ssi` 
                        LEFT JOIN `".Inc\Mains\Tables\EmployeeScheduleBreak::get_table_name()."` AS `break` ON break.staff_schedule_id = ssi.id
                        WHERE ssi.staff_id IN (".implode(',', array_keys($this->staff)) .") 
                            AND `ssi`.`start_time` IS NOT NULL"
            ,
            ARRAY_A
        );
        foreach ($working_schedule as $item) {
            $weekday = $item['day_index'] - 1;
            $schedule = $this->staff[$item['staff_id']]->get_schedule();
            if (!$schedule->has_day($weekday)) {
                $schedule->add_day($weekday, $item['start_time'], $item['end_time']);
            }
            if ($item['break_start']) {
                $schedule->add_break($item['day_index'] - 1, $item['break_start'], $item['break_end']);
            }
        }
    }

    private function handle_bookings()
    {
        // Prepare padding_left for first service.
        $sequence = $this->user_data->sequence->get_items();
        $first_item = $sequence[0];
        $services = $first_item->get_services();
        $first_service = $services[0];
        $padding_left = $first_service->get_padding_left();

        // Take into account the statuses.
        $statuses = array(
            Inc\Mains\Tables\CustomerBooking::STATUS_PENDING,
            Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED,
        );
        // Bookings
        $bookings = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT 
                            `a`.`id`,
                            `a`.`staff_id`,
                            `a`.`service_id`,
                            `a`.`start_date`,
                            `a`.`end_date`,
                            COALESCE(`s`.`padding_left`,0) AS `padding_left`,
                            COALESCE(`s`.`padding_right`,0) AS `padding_right`,
                            SUM(`ca`.`number_of_persons`) AS `number_of_bookings` 
                        FROM `".Inc\Mains\Tables\Booking::get_table_name()."` AS `a` 
                        LEFT JOIN `".Inc\Mains\Tables\CustomerBooking::get_table_name()."` AS `ca` ON `ca`.`booking_id` = `a`.`id` 
                        LEFT JOIN `".Inc\Mains\Tables\EmployeeService::get_table_name()."` AS `ss` ON `ss`.`staff_id` = `a`.`staff_id` AND `ss`.`service_id` = `a`.`service_id` 
                        LEFT JOIN `".Inc\Mains\Tables\Service::get_table_name()."` AS `s` ON `s`.`id` = `a`.`service_id` 
                        WHERE `a`.`staff_id` IN (".implode(',', array_keys($this->staff)) .") 
                            AND `ca`.`status` IN ('".implode("','", $statuses) ."') 
                            AND (DATE_ADD( `a`.`end_date`, INTERVAL (`padding_right` + %d) SECOND) >= %s) 
                        GROUP BY a.id",
                $padding_left,
                $this->start_dp->format('Y-m-d')
            ),
            ARRAY_A
        );
        foreach ($bookings as $booking) {
            $this->staff[$booking['staff_id']]->add_booking(new DataHolders\Booking(
                $booking['service_id'],
                $booking['number_of_bookings'],
                $booking['start_date'],
                $booking['end_date'],
                $booking['padding_left'],
                $booking['padding_right'],
                false
            ));
        }
    }

    private function handle_holidays()
    {
        $holidays = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT 
                            IF(h.repeat_event, DATE_FORMAT(h.date, '%%m-%%d'), h.date) as date, h.staff_id 
                        FROM `".Inc\Mains\Tables\Holiday::get_table_name()."` AS `h` 
                        WHERE h.staff_id IN (".implode(',', array_keys($this->staff)) .") 
                            AND (h.repeat_event = 1 OR h.date >= %s)",
                $this->start_dp->format('Y-m-d')
            ),
            ARRAY_A
        );

        foreach ($holidays as $holiday) {
            $this->staff[$holiday['staff_id']]->get_schedule()->add_holiday($holiday['date']);
        }
    }

    private function handle_cart_bookings()
    {
        foreach ($this->user_data->cart->get_items() as $cart_key => $cart_item) {
            if (!in_array($cart_key, $this->user_data->get('edit_cart_keys'))) {
                foreach ($cart_item->get('slots') as $slot) {
                    list ($service_id, $staff_id, $datetime) = $slot;
                    if (isset ($this->staff[$staff_id])) {
                        $service = Inc\Mains\Tables\Service::find($service_id);
                        $time_slot = TimeSlot::from_dates($datetime, $datetime);
                        $time_slot = $time_slot->resize($service->get_duration());
                        $booking_exists = false;
                        foreach ($this->staff[$staff_id]->get_bookings() as $booking) {
                            // If such booking exists increase number_of_bookings.
                            if ($booking->is_from_google() == false
                                && $booking->get_service_id() == $service_id
                                && $booking->get_time_slot()->wraps($time_slot)
                            ) {
                                $booking->inc_nop($cart_item->get('number_of_persons'));
                                $booking_exists = true;
                                break;
                            }
                        }
                        if (!$booking_exists) {
                            // Add cart item to staff bookings array.
                            $this->staff[$staff_id]->add_booking(new DataHolders\Booking(
                                $service_id,
                                $cart_item->get('number_of_persons'),
                                $time_slot->start()->format('Y-m-d H:i:s'),
                                $time_slot->end()->format('Y-m-d H:i:s'),
                                $service->get_padding_left(),
                                $service->get_padding_right(),
                                false
                            ));
                        }
                    }
                }
            }
        }
    }

    private function handle_google_calendar(){
        if (get_option('bookme_gc_remove_busy_slots')) {
            $data = $this->wpdb->get_results(
                    "SELECT * FROM `".Inc\Mains\Tables\Employee::get_table_name()."` 
                        WHERE id IN (".implode(',', array_keys($this->staff)) .") 
                            AND `google_data` IS NOT NULL",
                ARRAY_A
            );
            $data = Inc\Mains\Functions\System::bind_data_with_table( Inc\Mains\Tables\Employee::class, $data);
            /* @var Inc\Mains\Tables\Employee $staff */
            foreach ($data as $staff) {
                $google = new Inc\Mains\Google();
                if ($google->load_by_staff($staff)) {
                    foreach ($google->get_calendar_events($this->start_dp->value()) ?: array() as $booking) {
                        $this->staff[$staff->get_id()]->add_booking($booking);
                    }
                }
            }
        }
    }
}