<?php

namespace Bookme\App\Admin;

use Bookme\Inc;

/**
 * Class Appearance
 */
class Appearance extends Inc\Core\App
{

    const page_slug = 'bookme-appearance';

    /**
     * execute page.
     */
    public function execute()
    {
        $assets = BOOKME_URL . 'assets/admin/';
        $pubilc_assets = BOOKME_URL . 'assets/front/';

        Fragments::enqueue_global();
        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);

        wp_enqueue_style('bookme', $pubilc_assets . '/css/bookme.css', array(), BOOKME_VERSION);
        if (is_rtl()) {
            wp_enqueue_style('bookme-rtl', $pubilc_assets . '/css/bookme-rtl.css', array(), BOOKME_VERSION);
        }
        wp_enqueue_style('bookme-color-picker', $assets . 'css/color-picker.min.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-moment-js', $assets . '/js/moment.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-clndr-js', $pubilc_assets . '/js/clndr.js', array('jquery', 'underscore', 'bookme-moment-js'), BOOKME_VERSION);

        wp_enqueue_script('bookme-color-picker-js', $assets . 'js/color-picker.es5.min.js', array(), BOOKME_VERSION);
        wp_enqueue_script('bookme-side-panel-js', $assets . 'js/sidePanel.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-appearance', $assets . 'js/pages/appearance.js', array('jquery'), BOOKME_VERSION);

        global $wp_locale;

        $custom_css = get_option('bookme_form_custom_css');

        wp_localize_script('bookme-appearance', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'saved' => esc_html__('Settings have been saved.', 'bookme'),
            'months' => array_values($wp_locale->month),
            'days' => array_values($wp_locale->weekday),
            'daysShort' => array_values($wp_locale->weekday_abbrev),
            'start_of_week' => (int)get_option('start_of_week'),
            'is_rtl' => (int)is_rtl(),
            'custom_css' => $custom_css
        ));

        Inc\Core\Template::create('appearance/page')->display(compact('custom_css'));
    }

    /**
     * Save appearance settings
     */
    public function perform_update_appearance()
    {
        $options = array(
            'bookme_primary_color',
            'bookme_secondary_color',
            'bookme_show_progress_bar',
            'bookme_employee_name_with_price',
            'bookme_service_name_with_duration',
            'bookme_form_layout'
        );
        foreach ($options as $option) {
            update_option($option, Inc\Mains\Functions\Request::get_parameter($option));
        }

        wp_send_json_success();
    }

    /**
     * save custom css
     */
    public function perform_save_custom_css()
    {
        update_option('bookme_form_custom_css', Inc\Mains\Functions\Request::get_parameter('custom_css'));

        wp_send_json_success(array('message' => esc_html__('Custom CSS has been saved. Please refresh the page to see the changes.', 'bookme')));
    }
}