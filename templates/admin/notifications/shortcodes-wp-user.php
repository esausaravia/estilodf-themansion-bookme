<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
$codes = array(
    array('code' => 'customer_name', 'description' => esc_html__('full name of customer', 'bookme')),
    array('code' => 'customer_first_name', 'description' => esc_html__('first name of customer', 'bookme')),
    array('code' => 'customer_last_name', 'description' => esc_html__('last name of customer', 'bookme')),
    array('code' => 'customer_email', 'description' => esc_html__('email of customer', 'bookme')),
    array('code' => 'customer_phone', 'description' => esc_html__('phone of customer', 'bookme')),
    array('code' => 'company_name', 'description' => esc_html__('name of your company', 'bookme')),
    array('code' => 'company_logo', 'description' => esc_html__('your company logo', 'bookme')),
    array('code' => 'company_phone', 'description' => esc_html__('your company phone', 'bookme')),
    array('code' => 'company_website', 'description' => esc_html__('this web-site address', 'bookme')),
    array('code' => 'company_address', 'description' => esc_html__('address of your company', 'bookme')),
    array('code' => 'new_username', 'description' => esc_html__('customer new username', 'bookme')),
    array('code' => 'new_password', 'description' => esc_html__('customer new password', 'bookme')),
    array('code' => 'site_address', 'description' => esc_html__('site address', 'bookme')),
);
\Bookme\Inc\Mains\Functions\System::shortcodes($codes);