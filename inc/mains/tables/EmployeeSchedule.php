<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class EmployeeSchedule
 */
class EmployeeSchedule extends Inc\Core\Table
{
    /** @var  int */
    protected $staff_id;
    /** @var  int */
    protected $day_index;
    /** @var  int */
    protected $start_time;
    /** @var  int */
    protected $end_time;

    protected static $table = 'bm_employee_schedules';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'staff_id' => array('format' => '%d', 'reference' => array('table' => 'Employee')),
        'day_index' => array('format' => '%d'),
        'start_time' => array('format' => '%s'),
        'end_time' => array('format' => '%s'),
    );

    public function save()
    {
        $list = $this->get_breaks_list();
        foreach ($list as $row) {
            $break = new EmployeeScheduleBreak();
            $break->set_fields($row);
            if (
                $this->get_start_time() >= $break->get_start_time()
                || $break->get_start_time() >= $this->get_end_time()
                || $this->get_start_time() >= $break->get_end_time()
                || $break->get_end_time() >= $this->get_end_time()
            ) {
                $break->delete();
            }
        }

        parent::save();
    }

    /**
     * Checks if break is available
     *
     * @param $start_time
     * @param $end_time
     * @param $break_id
     * @return bool
     */
    public function is_break_available($start_time, $end_time, $break_id = 0)
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        return $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM `" . Inc\Mains\Tables\EmployeeScheduleBreak::get_table_name() . "`
                     WHERE staff_schedule_id = %d 
                         AND id != %d 
                         AND start_time < %s 
                         AND end_time > %s",
                    $this->get_id(), $break_id, $end_time, $start_time
                )
            ) == 0;
    }

    /**
     * Get list of breaks
     *
     * @return array
     */
    public function get_breaks_list()
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `" . EmployeeScheduleBreak::get_table_name() . "` 
                WHERE `staff_schedule_id` = %d
                ORDER BY start_time, end_time ASC",
                $this->get_id()
            )
        );
    }

    /**
     * Get staff_id
     *
     * @return int
     */
    public function get_staff_id()
    {
        return $this->staff_id;
    }

    /**
     * Set staff_id
     *
     * @param int $staff_id
     * @return $this
     */
    public function set_staff_id($staff_id)
    {
        $this->staff_id = $staff_id;

        return $this;
    }

    /**
     * Get day_index
     *
     * @return int
     */
    public function get_day_index()
    {
        return $this->day_index;
    }

    /**
     * Set day_index
     *
     * @param int $day_index
     * @return $this
     */
    public function set_day_index($day_index)
    {
        $this->day_index = $day_index;

        return $this;
    }

    /**
     * Get start_time
     *
     * @return int
     */
    public function get_start_time()
    {
        return $this->start_time;
    }

    /**
     * Set start_time
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
     * Get end_time
     *
     * @return int
     */
    public function get_end_time()
    {
        return $this->end_time;
    }

    /**
     * Set end_time
     *
     * @param int $end_time
     * @return $this
     */
    public function set_end_time($end_time)
    {
        $this->end_time = $end_time;

        return $this;
    }
}