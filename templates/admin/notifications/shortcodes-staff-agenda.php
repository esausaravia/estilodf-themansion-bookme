<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
$codes = array(
    array('code' => 'employee_name', 'description' => esc_html__('name of employee', 'bookme')),
    array('code' => 'next_day_agenda', 'description' => esc_html__('employee agenda for next day', 'bookme')),
    array('code' => 'tomorrow_date', 'description' => esc_html__('date of next day', 'bookme')),
);
\Bookme\Inc\Mains\Functions\System::shortcodes($codes);