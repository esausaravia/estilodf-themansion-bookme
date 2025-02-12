<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
$codes = array(
    array('code' => 'cart_info', 'description' => __('cart information', 'bookme')),
    array('code' => 'cart_info_c', 'description' => __('cart information with cancel', 'bookme')),
    array('code' => 'payment_type', 'description' => __('payment type', 'bookme')),
    array('code' => 'total_price', 'description' => __('total price of booking (sum of all cart items after applying coupon)', 'bookme')),
    array('code' => 'customer_name', 'description' => __('full name of customer', 'bookme')),
    array('code' => 'customer_first_name', 'description' => __('first name of customer', 'bookme')),
    array('code' => 'customer_last_name', 'description' => __('last name of customer', 'bookme')),
    array('code' => 'customer_email', 'description' => __('email of customer', 'bookme')),
    array('code' => 'customer_phone', 'description' => __('phone of customer', 'bookme')),
    array('code' => 'company_name', 'description' => __('name of company', 'bookme')),
    array('code' => 'company_phone', 'description' => __('company phone', 'bookme')),
    array('code' => 'company_website', 'description' => __('company web-site address', 'bookme')),
    array('code' => 'company_logo', 'description' => __('company logo', 'bookme')),
    array('code' => 'company_address', 'description' => __('address of company', 'bookme')),
);
\Bookme\Inc\Mains\Functions\System::shortcodes($codes);