<?php
/*
Plugin Name: Bookme
Plugin URI: https://bookme.bylancer.com/
Description: Bookme is a responsive, multipurpose and fully customizable WordPress appointment booking and scheduling software for accepting online appointments bookings & scheduling.
Version: 4.8
Author: Bylancer
Author URI: https://bylancer.com/
Text Domain: bookme
Domain Path: /languages
License: Commercial
*/

namespace Bookme;

use Bookme\Inc\Mains\Functions\System;

defined('ABSPATH') or die('No script kiddies please!'); // No direct access

define('BOOKME_VERSION', '4.8');
define('BOOKME_PLUGIN_SLUG', plugin_basename(__FILE__));
define('BOOKME_PATH', plugin_dir_path(__FILE__));
define('BOOKME_URL', plugin_dir_url(__FILE__));

if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50500) {
    function bookme_php_outdated()
    {
        echo '<div class="updated"><h3>Bookme</h3><p>To install the plugin - <strong>PHP 5.5</strong> or higher is required.</p></div>';
    }

    add_action(is_network_admin() ? 'network_admin_notices' : 'admin_notices', 'Bookme\bookme_php_outdated');
} else {

    include_once __DIR__ . '/inc/autoload.php';

    /**
     * Plugin Class
     */
    class BookmePlugin
    {

        /**
         * Init.
         */
        public function init()
        {
            if (!session_id()) {
                // WP 4.9+ fix loopback request failure and WP Site Health
                if (!isset($_GET['wp_scrape_key']) &&
                    !(isset($_POST['action']) && strncmp($_POST['action'], 'health-check-', 13) === 0)) {
                    // Start session.
                    @session_start();
                }
            }

            if (!is_admin()) {
                // Payments ( PayPal Express Checkout, 2Checkout, Mollie )
                if (isset($_REQUEST['bookme_action'])) {
                    // Disable caching.
                    Inc\Mains\Functions\System::no_cache();

                    switch ($_REQUEST['bookme_action']) {
                        // PayPal Express Checkout.
                        case 'paypal-ec-init':
                            $this->paypalApp->ec_init();
                            break;
                        case 'paypal-ec-return':
                            $this->paypalApp->ec_return();
                            break;
                        case 'paypal-ec-cancel':
                            $this->paypalApp->ec_cancel();
                            break;
                        case 'paypal-ec-error':
                            $this->paypalApp->ec_error();
                            break;
                        // 2Checkout.
                        case '2checkout-approved':
                            $this->twoCheckoutApp->approved();
                            break;
                        case '2checkout-error':
                            $this->twoCheckoutApp->error();
                            break;
                        // Mollie.
                        case 'mollie-checkout':
                            $this->mollieApp->checkout();
                            break;
                        case 'mollie-response':
                            $this->mollieApp->response();
                            break;
                        case 'mollie-ipn':
                            Inc\Payment\Mollie::ipn();
                            break;
                    }
                }
            }
        }

        /**
         * Admin menu.
         */
        public function add_admin_menu()
        {
            global $current_user, $submenu, $wpdb;

            if (
                $current_user->has_cap('administrator') ||
                $current_user->has_cap('manage_bookme') ||
                $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` WHERE `wp_user_id` = %d", $current_user->ID))
            ) {
                add_menu_page('Bookme', 'Bookme', 'read', 'bookme-menu', '',
                    BOOKME_URL . 'assets/admin/images/menu-logo.png', 40);

                // sub-menu pages.
                $dashboard = esc_html__('Dashboard', 'bookme');
                $services = esc_html__('Services', 'bookme');
                $staff_members = esc_html__('Staff Members', 'bookme');
                $appointments = esc_html__('All Bookings', 'bookme');
                $calendar = esc_html__('Calendar', 'bookme');
                $customers = esc_html__('Customers', 'bookme');
                $payments = esc_html__('Payments', 'bookme');
                $appearance = esc_html__('Appearance', 'bookme');
                $notifications = esc_html__('Notifications', 'bookme');
                $custom_fields = esc_html__('Custom Fields', 'bookme');
                $coupons = esc_html__('Coupons', 'bookme');
                $settings = esc_html__('Settings', 'bookme');

                $capability = current_user_can( 'manage_options' ) ? 'manage_options' : 'manage_bookme';

                add_submenu_page('bookme-menu', $dashboard, $dashboard, $capability,
                    App\Admin\Dashboard::page_slug, array($this->dashboard_app, 'execute'));

                if ($current_user->has_cap('administrator')) {
                    add_submenu_page('bookme-menu', $staff_members, $staff_members, $capability,
                        App\Admin\Employees::page_slug, array($this->employee_app, 'execute'));
                } else {
                    if (get_option('bookme_allow_staff_edit_profile') == 1) {
                        add_submenu_page('bookme-menu', esc_html__('Profile', 'bookme'), esc_html__('Profile', 'bookme'), 'read',
                            App\Admin\Employees::page_slug, array($this->employee_app, 'execute'));
                    }
                }

                add_submenu_page('bookme-menu', $services, $services, $capability,
                    App\Admin\Services::page_slug, array($this->service_app, 'execute'));
                add_submenu_page('bookme-menu', $appointments, $appointments, $capability,
                    App\Admin\Bookings::page_slug, array($this->bookings_app, 'execute'));
                add_submenu_page('bookme-menu', $calendar, $calendar, 'read',
                    App\Admin\Calendar::page_slug, array($this->calendar_app, 'execute'));
                add_submenu_page('bookme-menu', $customers, $customers, $capability,
                    App\Admin\Customers::page_slug, array($this->customer_app, 'execute'));
                add_submenu_page('bookme-menu', $payments, $payments, $capability,
                    App\Admin\Payments::page_slug, array($this->payment_app, 'execute'));
                add_submenu_page('bookme-menu', $appearance, $appearance, $capability,
                    App\Admin\Appearance::page_slug, array($this->apearance_app, 'execute'));
                add_submenu_page('bookme-menu', $notifications, $notifications, $capability,
                    App\Admin\Notifications::page_slug, array($this->notifications_app, 'execute'));
                add_submenu_page('bookme-menu', $custom_fields, $custom_fields, $capability,
                    App\Admin\CustomFields::page_slug, array($this->custom_fields_app, 'execute'));
                add_submenu_page('bookme-menu', $coupons, $coupons, $capability,
                    App\Admin\Coupons::page_slug, array($this->coupons_app, 'execute'));
                add_submenu_page('bookme-menu', $settings, $settings, $capability,
                    App\Admin\Settings::page_slug, array($this->settings_app, 'execute'));

                unset($submenu['bookme-menu'][0]);
            }
        }

        /**
         * Add the Bookme Button for shortcode
         */
        public function shortcode_button()
        {
            new App\Admin\ShortcodeButton();
        }

        /**
         * Enqueue frontend assets
         */
        public function enqueue_assets()
        {
            $assets = BOOKME_URL . 'assets/front';
            $admin_assets = BOOKME_URL . 'assets/admin';

            // load assets for booking form

            if (get_option('bookme_phone_default_country') != 'disabled') {
                wp_enqueue_style('bookme-intlTelInput', $assets . '/css/intlTelInput.css', array(), BOOKME_VERSION);
            }
            wp_enqueue_style('bookme-scroll', $assets . '/css/trackpad-scroll.css', array(), BOOKME_VERSION);

            wp_enqueue_style('bookme', $assets . '/css/bookme.css', array(), BOOKME_VERSION);
            if (is_rtl()) {
                wp_enqueue_style('bookme-rtl', $assets . '/css/bookme-rtl.css', array(), BOOKME_VERSION);
            }

            wp_enqueue_script('bookme-scroll', $assets . '/js/jquery.scroll.min.js', array('jquery'), BOOKME_VERSION);
            if (get_option('bookme_phone_default_country') != 'disabled') {
                wp_enqueue_script('bookme-intlTelInput-js', $assets . '/js/intlTelInput.min.js', array('jquery'), BOOKME_VERSION);
            }
            wp_enqueue_script('bookme-moment-js', $admin_assets . '/js/moment.min.js', array('jquery'), BOOKME_VERSION);
            wp_enqueue_script('bookme-clndr-js', $assets . '/js/clndr.js', array('jquery', 'underscore', 'bookme-moment-js'), BOOKME_VERSION);

            if (get_option('bookme_captcha_enabled')) {
                wp_enqueue_script('bookme-google-recaptcha-js', 'https://www.google.com/recaptcha/api.js?render=explicit', array());
            }

            wp_enqueue_script('bookme-js', $assets . '/js/bookme.js', array('bookme-clndr-js', 'bookme-scroll'), BOOKME_VERSION);

            global $sitepress, $wp_locale;
            // AJAX url with WPML support
            $ajax_url = admin_url('admin-ajax.php');
            if ($sitepress instanceof \SitePress) {
                $ajax_url .= (strpos($ajax_url, '?') ? '&' : '?') . 'lang=' . $sitepress->get_current_language();
            }

            $woocommerce_enabled = (int)Inc\Mains\Functions\System::woo_commerce_enabled();
            $intlTelInput = array('enabled' => 0);
            if (get_option('bookme_phone_default_country') != 'disabled') {
                $intlTelInput['enabled'] = 1;
                $intlTelInput['utils'] = BOOKME_URL . 'assets/front/js/intlTelInput.utils.js';
                $intlTelInput['country'] = get_option('bookme_phone_default_country');
            }

            $required = array(
                'staff' => (int)get_option('bookme_required_employee')
            );

            wp_localize_script('bookme-js', 'Bookme', array(
                'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
                'months' => array_values($wp_locale->month),
                'days' => array_values($wp_locale->weekday),
                'daysShort' => array_values($wp_locale->weekday_abbrev),
                'start_of_week' => (int)get_option('start_of_week'),
                'ajaxurl' => $ajax_url,
                'required' => $required,
                'final_step_url' => get_option('bookme_final_step_url'),
                'intlTelInput' => $intlTelInput,
                'woocommerce' => array('enabled' => $woocommerce_enabled, 'cart_url' => $woocommerce_enabled ? wc_get_cart_url() : ''),
                'cart' => array('enabled' => $woocommerce_enabled ? 0 : (int)Inc\Mains\Functions\System::show_step_cart()),
                'stripe' => array('enabled' => (int)Inc\Mains\Functions\System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_STRIPE)),
                'razorpay' => array('enabled' => (int)Inc\Mains\Functions\System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_RAZORPAY)),
                'is_rtl' => (int)is_rtl(),
                'not_available' => esc_html__('Not Available', 'bookme'),
                'captcha_site_key' => get_option('bookme_captcha_site_key')
            ));

            // assets for customer bookings shortcode
            wp_enqueue_style('bookme', $assets . '/css/bookme.css', array(), BOOKME_VERSION);
            if (is_rtl()) {
                wp_enqueue_style('bookme-rtl', $assets . '/css/bookme-rtl.css', array(), BOOKME_VERSION);
            }
            wp_enqueue_script('customer-bookings-js', $assets . '/js/customer_bookings.js', array('jquery'), BOOKME_VERSION);

            wp_localize_script('customer-bookings-js', 'BookmeCB', array(
                'csrf_token' => Inc\Mains\Functions\System::get_security_token()
            ));
        }

        /**
         * Constructor.
         */
        public function __construct()
        {
            // Call all the hooks
            add_action('wp_loaded', array($this, 'init'));

            // Front Apps
            $this->bookingFormApp = App\Front\BookingForm::get_instance();
            $this->customerBookingsApp = App\Front\CustomerBookings::get_instance();

            // Apps for payment gateways
            $this->wooCommerceApp = App\Front\WooCommerce::get_instance();
            if (Inc\Mains\Functions\System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_PAYPAL)) {
                $this->paypalApp = App\Front\PayPal::get_instance();
            }
            if (Inc\Mains\Functions\System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_2CHECKOUT)) {
                $this->twoCheckoutApp = App\Front\TwoCheckout::get_instance();
            }
            if (Inc\Mains\Functions\System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_MOLLIE)) {
                $this->mollieApp = App\Front\Mollie::get_instance();
            }
            if (Inc\Mains\Functions\System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_AUTHORIZENET)) {
                $this->authorizeNetApp = App\Front\AuthorizeNet::get_instance();
            }
            if (Inc\Mains\Functions\System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_STRIPE)) {
                $this->stripeApp = App\Front\Stripe::get_instance();
            }
            if (Inc\Mains\Functions\System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_RAZORPAY)) {
                $this->razorpayApp = App\Front\Razorpay::get_instance();
            }

            if (is_admin()) {
                add_action('admin_menu', array($this, 'add_admin_menu'));
                add_action('admin_init', array($this, 'shortcode_button'));

                // for Site Health
                add_filter('site_status_tests', function ($tests) {
                    // Close current session, for fixing loopback request
                    session_write_close();
                    return $tests;
                }, 10, 1);

                // Admin Apps
                $this->dashboard_app = App\Admin\Dashboard::get_instance();
                $this->apearance_app = App\Admin\Appearance::get_instance();
                $this->bookings_app = App\Admin\Bookings::get_instance();
                $this->calendar_app = App\Admin\Calendar::get_instance();
                $this->coupons_app = App\Admin\Coupons::get_instance();
                $this->customer_app = App\Admin\Customers::get_instance();
                $this->custom_fields_app = App\Admin\CustomFields::get_instance();
                $this->notifications_app = App\Admin\Notifications::get_instance();
                $this->payment_app = App\Admin\Payments::get_instance();
                $this->service_app = App\Admin\Services::get_instance();
                $this->settings_app = App\Admin\Settings::get_instance();
                $this->employee_app = App\Admin\Employees::get_instance();
            } else {
                add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

                // Register shortcode
                add_shortcode('bookme', array($this->bookingFormApp, 'execute'));
                add_shortcode('bookme-customer-bookings', array($this->customerBookingsApp, 'execute'));
            }
        }
    }

    // run the plugin
    Inc\Mains\Plugin::run();
    new BookmePlugin();
}