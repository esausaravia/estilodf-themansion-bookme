<?php

namespace Bookme\Inc\Mains\Functions;

use Bookme\Inc;

/**
 * Class Common
 */
abstract class System
{
    /** @var string CSRF token */
    private static $csrf = null;
    /** @var string */
    private static $wp_timezone = null;

    /**
     * Generates email's headers
     *
     * @param array $extra
     * @return array
     */
    public static function get_email_headers($extra = array())
    {
        $headers = array();
        if (self::send_email_as_html()) {
            $headers[] = 'Content-Type: text/html; charset=utf-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=utf-8';
        }
        $headers[] = 'From: ' . get_option('bookme_email_sender_name') . ' <' . get_option('bookme_email_sender') . '>';
        if (isset ($extra['reply-to'])) {
            $headers[] = 'Reply-To: ' . $extra['reply-to']['name'] . ' <' . $extra['reply-to']['email'] . '>';
        }

        return apply_filters('bookme_email_headers', $headers);
    }

    /**
     * Get url of current page
     * @return string
     */
    public static function get_current_page_url()
    {
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            $url = 'https://';
        } else {
            $url = 'http://';
        }
        $url .= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];

        return $url . $_SERVER['REQUEST_URI'];
    }

    /**
     * Escape params for admin.php?page=$page_slug
     *
     * @param $page_slug
     * @param array $params
     * @return string
     */
    public static function esc_admin_url($page_slug, $params = array())
    {
        $path = 'admin.php?page=' . $page_slug;
        if (($query = build_query($params)) != '') {
            $path .= '&' . $query;
        }

        return esc_url(admin_url($path));
    }

    /**
     * Get option translated with WPML.
     *
     * @param $option_name
     * @return string
     */
    public static function get_translated_option($option_name)
    {
        return self::get_translated_string($option_name, get_option($option_name));
    }

    /**
     * Get string translated with WPML.
     *
     * @param string $name
     * @param string $original_value
     * @param null|string $language_code Return the translation in this language
     * @return string
     */
    public static function get_translated_string($name, $original_value = '', $language_code = null)
    {
        return apply_filters('wpml_translate_single_string', $original_value, 'bookme', $name, $language_code);
    }

    /**
     * Get custom fields
     *
     * @param integer $service_id
     * @return \stdClass[]
     */
    public static function get_custom_fields($service_id = null)
    {
        $custom_fields = json_decode(get_option('bookme_custom_fields'));
        foreach ($custom_fields as $key => $custom_field) {
            if ($service_id === null || in_array($service_id, $custom_field->services)) {
                switch ($custom_field->type) {
                    case 'checkboxes':
                    case 'radio-buttons':
                    case 'drop-down':
                        $items = $custom_field->items;
                        foreach ($items as $pos => $label) {
                            $items[$pos] = array(
                                'label' => $label,
                                'value' => $label,
                            );
                        }
                        $custom_field->items = $items;
                        break;
                }
            } else {
                unset($custom_fields[$key]);
            }
        }

        return $custom_fields;
    }

    /**
     * Get translated custom fields
     *
     * @param integer $service_id
     * @param string $language_code Return the translation in this language
     * @return \stdClass[]
     */
    public static function get_translated_custom_fields($service_id = null, $language_code = null)
    {
        $custom_fields = self::get_custom_fields($service_id);
        foreach ($custom_fields as $key => $custom_field) {
            if ($service_id === null || in_array($service_id, $custom_field->services)) {
                switch ($custom_field->type) {
                    case 'textarea':
                    case 'text-content':
                    case 'text-field':
                        $custom_field->label = self::get_translated_string('custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label), $custom_field->label, $language_code);
                        break;
                    case 'checkboxes':
                    case 'radio-buttons':
                    case 'drop-down':
                        $items = $custom_field->items;
                        foreach ($items as &$field) {
                            $field['label'] = self::get_translated_string('custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label) . '=' . sanitize_title($field['value']), $field['value'], $language_code);
                        }
                        $custom_field->label = self::get_translated_string('custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label), $custom_field->label, $language_code);
                        $custom_field->items = $items;
                        break;
                }
            } else {
                unset($custom_fields[$key]);
            }
        }

        return $custom_fields;
    }

    /**
     * Check whether the current user is administrator or not.
     *
     * @return bool
     */
    public static function is_current_user_admin()
    {
        return current_user_can('manage_options') || current_user_can('manage_bookme');
    }

    /**
     * Add hidden input with CSRF token.
     */
    public static function csrf()
    {
        printf(
            '<input type="hidden" name="csrf_token" value="%s">',
            esc_attr(self::get_security_token())
        );
    }

    /**
     * XOR encrypt/decrypt.
     *
     * @param string $str
     * @param string $password
     * @return string
     */
    private static function _xor($str, $password = '')
    {
        $len = strlen($str);
        $gamma = '';
        $n = $len > 100 ? 8 : 2;
        while (strlen($gamma) < $len) {
            $gamma .= substr(pack('H*', sha1($password . $gamma)), 0, $n);
        }

        return $str ^ $gamma;
    }

    /**
     * XOR encrypt with Base64 encode.
     *
     * @param string $str
     * @param string $password
     * @return string
     */
    public static function xor_encrypt($str, $password = '')
    {
        return base64_encode(self::_xor($str, $password));
    }

    /**
     * XOR decrypt with Base64 decode.
     *
     * @param string $str
     * @param string $password
     * @return string
     */
    public static function xor_decrypt($str, $password = '')
    {
        return self::_xor(base64_decode($str), $password);
    }

    /**
     * Codes list helper
     *
     * @param array $codes
     * @param array $flags
     */
    public static function shortcodes(array $codes, $flags = array())
    {
        $tpl = '';
        foreach ($codes as $code) {
            $valid = true;
            if (isset ($code['flags'])) {
                foreach ($code['flags'] as $flag => $value) {
                    $valid = false;
                    if (isset ($flags[$flag])) {
                        if (is_string($value) && preg_match('/([!>=<]+)(\d+)/', $value, $match)) {
                            switch ($match[1]) {
                                case '<':
                                    $valid = $flags[$flag] < $match[2];
                                    break;
                                case '<=':
                                    $valid = $flags[$flag] <= $match[2];
                                    break;
                                case '=':
                                    $valid = $flags[$flag] == $match[2];
                                    break;
                                case '!=':
                                    $valid = $flags[$flag] != $match[2];
                                    break;
                                case '>=':
                                    $valid = $flags[$flag] >= $match[2];
                                    break;
                                case '>':
                                    $valid = $flags[$flag] > $match[2];
                                    break;
                            }
                        } else {
                            $valid = $flags[$flag] == $value;
                        }
                    }
                    if (!$valid) {
                        break;
                    }
                }
            }
            if ($valid) {
                $tpl .= sprintf(
                    '<div class="bm-shortcode-box">
                                <div class="bg-light" title="%s" data-tippy-placement="top">{%s}</div>
                                <button class="btn-icon" title="%s" data-tippy-placement="top" type="button" data-code="{%s}"><i class="icon-feather-copy"></i></button>
                            </div>',
                    $code['description'],
                    $code['code'],
                    esc_attr__('Copy', 'bookme'),
                    $code['code']
                );
            }
        }

        echo "<div class='bm-shortcode-wrapper'>$tpl</div>";
    }

    /**
     * Generate unique token for table field
     *
     * @param string $table_class_name
     * @param string $token_field
     * @return string
     */
    public static function generate_token($table_class_name, $token_field)
    {
        /** @var Inc\Core\Table $table */
        $table = new $table_class_name();
        do {
            $token = md5(uniqid(time(), true));
        } while ($table->load_by(array($token_field => $token)) === true);

        return $token;
    }


    /**
     * Get Security token
     *
     * @return string
     */
    public static function get_security_token()
    {
        if (self::$csrf === null) {
            self::$csrf = wp_create_nonce('bookme');
        }

        return self::$csrf;
    }

    /**
     * Verify Security token
     *
     * @return string
     */
    public static function verify_security_token()
    {
        return wp_verify_nonce(Inc\Mains\Functions\Request::get_parameter('csrf_token'), 'bookme') == 1;
    }

    /**
     * Set nocache constants.
     */
    public static function no_cache()
    {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        if (!defined('DONOTCACHEOBJECT')) {
            define('DONOTCACHEOBJECT', true);
        }
        if (!defined('DONOTCACHEDB')) {
            define('DONOTCACHEDB', true);
        }
    }

    /**
     * Generate a random string of a length
     * @param int $length
     * @return string
     */
    public static function generate_random_string($length = 10)
    {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }

    /**
     * Check if Gutenberg is active on the page
     * @return bool
     */
    public static function is_gutenberg_page()
    {
        if (function_exists('is_gutenberg_page') &&
            is_gutenberg_page()
        ) {
            // The Gutenberg plugin is on.
            return true;
        }
        $current_screen = get_current_screen();
        if (method_exists($current_screen, 'is_block_editor') &&
            $current_screen->is_block_editor()
        ) {
            // Gutenberg page on 5+.
            return true;
        }
        return false;
    }

    /**
     * Get categories, services and staff members for drop down selects
     * for the 1st step of booking wizard.
     *
     * @return array
     */
    public static function get_categories_services_staffs()
    {
        $data = array(
            'categories' => array(),
            'services' => array(),
            'staff' => array(),
        );

        global $wpdb;

        // Categories
        $result = $wpdb->get_results("SELECT * FROM `" . Inc\Mains\Tables\Category::get_table_name() . "`", ARRAY_A);
        foreach ($result as $info) {
            $data['categories'][$info['id']] = array(
                'id' => (int)$info['id'],
                'name' => self::get_translated_string('category_' . $info['id'], $info['name']),
                'position' => (int)$info['position'],
            );
        }

        // Services
        $result = $wpdb->get_results("SELECT 
                    s.id, s.category_id, s.title, s.position, s.duration, MIN(ss.capacity_min) AS min_capacity, MAX(ss.capacity_max) AS max_capacity 
                    FROM `" . Inc\Mains\Tables\Service::get_table_name() . "` AS `s` 
                    INNER JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` AS `ss` ON ss.service_id = s.id 
                    WHERE `s`.`visibility` != 'private' 
                    GROUP BY s.id",
            ARRAY_A);
        foreach ($result as $info) {
            $data['services'][$info['id']] = array(
                'id' => (int)$info['id'],
                'category_id' => (int)$info['category_id'],
                'name' => self::get_translated_string('service_' . $info['id'], $info['title']),
                'duration' => Inc\Mains\Functions\DateTime::seconds_to_interval($info['duration']),
                'min_capacity' => (int)$info['min_capacity'],
                'max_capacity' => (int)$info['max_capacity'],
                'position' => (int)$info['position'],
            );
        }

        // Employees
        $result = $wpdb->get_results("SELECT 
                    st.id, st.full_name, st.position, ss.service_id, ss.capacity_min, ss.capacity_max, ss.price 
                    FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` AS `st` 
                    INNER JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` AS `ss` ON ss.staff_id = st.id 
                    LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` AS `s` ON s.id = ss.service_id 
                    WHERE `st`.`visibility` != 'private' AND `s`.`visibility` != 'private'",
            ARRAY_A);
        foreach ($result as $info) {
            if (!isset ($data['staff'][$info['id']])) {
                $data['staff'][$info['id']] = array(
                    'id' => (int)$info['id'],
                    'name' => self::get_translated_string('staff_' . $info['id'], $info['full_name']),
                    'services' => array(),
                    'position' => (int)$info['position'],
                );
            }
            $data['staff'][$info['id']]['services'][$info['service_id']] = array(
                'id' => (int)$info['service_id'],
                'min_capacity' => (int)$info['capacity_min'],
                'max_capacity' => (int)$info['capacity_max'],
                'price' => get_option('bookme_employee_name_with_price')
                    ? html_entity_decode(Inc\Mains\Functions\Price::format($info['price']))
                    : null,
            );
        }

        return $data;
    }

    /**
     * Get array with min and max date for the calendar
     *
     * @return array
     */
    public static function get_min_max_date_for_calendar()
    {
        $result = array();

        $dp = Date::now()->modify(self::get_minimum_time_prior_booking())->to_client_tz();
        $result['date_min'] =
            $dp->format('Y') . '-' .
            $dp->format('n') . '-' .
            $dp->format('j');

        $dp = $dp->modify((self::get_maximum_available_days_for_booking() - 1) . ' days');
        $result['date_max'] =
            $dp->format('Y') . '-' .
            $dp->format('n') . '-' .
            $dp->format('j');

        return $result;
    }

    /**
     * Bind wpdb data with table class
     * @param string $table
     * @param array $data
     * @param string|null $index_by
     * @return Inc\Core\Table[]
     */
    public static function bind_data_with_table($table, array $data, $index_by = null)
    {
        $results = array();
        foreach ($data as $index => $array) {
            $i = $index_by !== null ? $array[$index_by] : $index;
            /** @var Inc\Core\Table[] $results */
            $results[$i] = new $table();
            $results[$i]->set_fields($array, true);
        }
        return $results;
    }

    /**
     * Create option fields for schedule
     * @param string|null $selected_value
     * @param string $type
     * @return string
     */
    public static function schedule_options($selected_value = null, $type = 'to')
    {
        $ts_length = self::get_time_slot_length();
        $time_start = 0;
        $time_end = DAY_IN_SECONDS;

        if ($type == 'from') {
            $time_end -= $ts_length;    // Exclude last slot.
        } else if ($type == 'break_from') {
            $time_end *= 2;             // Create slots for 2 days.
            $time_end -= $ts_length;    // Exclude last slot.
        } else if ($type == 'to') {
            $time_end *= 2;             // Create slots for 2 days.
        }

        $options = '';
        $value_added = false;
        $selected_value_seconds = DateTime::time_to_seconds($selected_value);
        while ($time_start <= $time_end) {
            if ($value_added === false) {
                if ($selected_value_seconds == $time_start) {
                    $value_added = true;
                } elseif ($selected_value_seconds < $time_start) {
                    // Make sure that value presents in the list,
                    // even if corresponding option did not exist in $this->values.
                    $options .= sprintf(
                        '<option value="%s" selected="selected">%s</option>',
                        $selected_value,
                        DateTime::format_time($selected_value_seconds)
                    );
                    $value_added = true;
                }
            }
            $time_start_string = DateTime::build_time_string($time_start);
            $options .= sprintf(
                '<option value="%s"%s>%s</option>',
                $time_start_string,
                selected($selected_value, $time_start_string, false),
                DateTime::format_time($time_start)
            );

            $time_start += $ts_length;
        }
        return $options;
    }

    /**
     * Get value of option for given payment type.
     *
     * @param string $type
     * @return string
     */
    public static function get_payment_type_option($type)
    {
        return get_option('bookme_' . $type . '_enabled', 'disabled');
    }

    /**
     * Check whether given payment type is enabled.
     *
     * @param string $type
     * @return bool
     */
    public static function payment_type_enabled($type)
    {
        return self::get_payment_type_option($type) != 'disabled';
    }

    /**
     * Check whether payment step is disabled.
     *
     * @return bool
     */
    public static function payment_disabled()
    {
        $types = array(
            Inc\Mains\Tables\Payment::TYPE_LOCAL,
            Inc\Mains\Tables\Payment::TYPE_PAYPAL,
            Inc\Mains\Tables\Payment::TYPE_STRIPE,
            Inc\Mains\Tables\Payment::TYPE_2CHECKOUT,
            Inc\Mains\Tables\Payment::TYPE_AUTHORIZENET,
            Inc\Mains\Tables\Payment::TYPE_MOLLIE,
        );

        foreach ($types as $type) {
            if (self::payment_type_enabled($type)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get time slot length in seconds.
     *
     * @return integer
     */
    public static function get_time_slot_length()
    {
        return (int)get_option('bookme_time_slot_step', 15) * MINUTE_IN_SECONDS;
    }

    /**
     * Check whether service duration should be used instead of slot length on the frontend.
     *
     * @return bool
     */
    public static function service_duration_as_slot_length()
    {
        return (bool)get_option('bookme_service_duration_as_slot_step', false);
    }

    /**
     * Check whether use client time zone.
     *
     * @return bool
     */
    public static function use_client_time_zone()
    {
        return (bool)get_option('bookme_use_client_time_zone');
    }

    /**
     * Get minimum time (in seconds) prior to booking.
     *
     * @return integer
     */
    public static function get_minimum_time_prior_booking()
    {
        return (int)(get_option('bookme_min_time_before_booking') * 3600);
    }

    /**
     * @return int
     */
    public static function get_maximum_available_days_for_booking()
    {
        return (int)get_option('bookme_max_days_for_booking', 365);
    }

    /**
     * Whether to use first and last customer name instead full name.
     *
     * @return bool
     */
    public static function show_first_last_name()
    {
        return (bool)get_option('bookme_customer_first_last_name', false);
    }

    /**
     * Whether phone field is required at the Details step or not.
     *
     * @return bool
     */
    public static function phone_required()
    {
        return get_option('bookme_customer_required_phone') == 1;
    }

    /**
     * Whether custom fields attached to services or not.
     *
     * @return bool
     */
    public static function custom_fields_per_service()
    {
        return get_option('bookme_custom_fields_per_service') == 1;
    }

    /**
     * Whether combined notifications for cart are enabled or not.
     *
     * @return bool
     */
    public static function combined_notifications_enabled()
    {
        return get_option('bookme_combined_notifications') == 1;
    }

    /**
     * Whether step Cart is enabled or not.
     *
     * @return bool
     */
    public static function show_step_cart()
    {
        return get_option('bookme_cart_enabled') == 1 && !self::woo_commerce_enabled();
    }

    /**
     * Check if emails are sent as HTML or plain text.
     *
     * @return bool
     */
    public static function send_email_as_html()
    {
        return get_option('bookme_email_send_as') == 'html';
    }

    /**
     * Get WordPress time zone setting.
     *
     * @return string
     */
    public static function get_wp_time_zone()
    {
        if (self::$wp_timezone === null) {
            if ($timezone = get_option('timezone_string')) {
                // If site timezone string exists, return it.
                self::$wp_timezone = $timezone;
            } else {
                // Otherwise return offset.
                $gmt_offset = get_option('gmt_offset');
                self::$wp_timezone = sprintf('%s%02d:%02d', $gmt_offset >= 0 ? '+' : '-', abs($gmt_offset), abs($gmt_offset) * 60 % 60);
            }
        }

        return self::$wp_timezone;
    }

    /**
     * WooCommerce Plugin enabled or not.
     *
     * @return bool
     */
    public static function woo_commerce_enabled()
    {
        return (get_option('bookme_wc_enabled') && get_option('bookme_wc_product') && class_exists('WooCommerce', false) && (wc_get_cart_url() !== false));
    }
}