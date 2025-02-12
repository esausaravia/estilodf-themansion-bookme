<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class ScheduleBreak
 */
class EmployeeScheduleBreak extends Inc\Core\Table
{
    /** @var  int */
    protected $staff_schedule_id;
    /** @var  int */
    protected $start_time;
    /** @var  int */
    protected $end_time;

    protected static $table = 'bm_employee_schedule_breaks';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'staff_schedule_id' => array('format' => '%d', 'reference' => array('table' => 'EmployeeSchedule')),
        'start_time' => array('format' => '%s'),
        'end_time' => array('format' => '%s'),
    );

    /**
     * Gets staff_schedule_id
     *
     * @return int
     */
    public function get_staff_schedule_id()
    {
        return $this->staff_schedule_id;
    }

    /**
     * Sets staff_schedule_id
     *
     * @param int $staff_schedule_id
     * @return $this
     */
    public function set_staff_schedule_id($staff_schedule_id)
    {
        $this->staff_schedule_id = $staff_schedule_id;

        return $this;
    }

    /**
     * Gets start_time
     *
     * @return int
     */
    public function get_start_time()
    {
        return $this->start_time;
    }

    /**
     * Sets start_time
     *
     * @param int $start_time
     * @return $this
     */
    public function set_start_time($start_time)
    {
        $this->start_time = $start_time;

        return $this;
    }

    /**
     * Gets end_time
     *
     * @return int
     */
    public function get_end_time()
    {
        return $this->end_time;
    }

    /**
     * Sets end_time
     *
     * @param int $end_time
     * @return $this
     */
    public function set_end_time($end_time)
    {
        $this->end_time = $end_time;

        return $this;
    }

    /**
     * Remove all breaks for staff member
     *
     * @param $staff_id
     */
    public function remove_breaks_by_staff_id($staff_id)
    {
        self::$wpdb->get_results(self::$wpdb->prepare(
            'DELETE `break` FROM `' . self::get_table_name() . '` AS `break`
            LEFT JOIN `' . EmployeeSchedule::get_table_name() . '` AS `item` ON `item`.`id` = `break`.`staff_schedule_id`
            WHERE `item`.`staff_id` = %d',
            $staff_id
        ));
    }
}