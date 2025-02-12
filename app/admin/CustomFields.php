<?php
namespace Bookme\App\Admin;

use Bookme\Inc;
/**
 * Class CustomFields
 */
class CustomFields extends Inc\Core\App {

    const page_slug = 'bookme-custom-fields';

    /**
     * execute page.
     */
    public function execute()
    {
        Fragments::enqueue_global();
        $assets = BOOKME_URL . 'assets/admin/';

        wp_enqueue_style('bookme-multi-select', $assets . 'css/jquery.multiselect.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-multi-select-js', $assets . 'js/jquery.multiselect.js', array(), BOOKME_VERSION);
        wp_enqueue_script('bookme-custom-fields', $assets . 'js/pages/custom_fields.js', array('jquery','jquery-ui-sortable'), BOOKME_VERSION);

        wp_localize_script('bookme-custom-fields', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'custom_fields' => get_option('bookme_custom_fields'),
            'are_you_sure' => esc_attr__('Are you sure?', 'bookme'),
            'saved' => esc_attr__('Custom fields have been saved.', 'bookme'),
        ));

        /** @var \wpdb $wpdb */
        global $wpdb;
        // all services
        $all_services = array();
        $data = $wpdb->get_results(
            "SELECT c.name AS category_name, s.* 
                FROM `" . Inc\Mains\Tables\Category::get_table_name() . "` c
                INNER JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` s ON s.category_id = c.id
                ORDER BY c.position, s.position",
            ARRAY_A
        );
        foreach ($data as $row) {
            $all_services[$row['category_name']][] = $row;
        }
        Inc\Core\Template::create('custom_fields/page')->display(compact('all_services'));
    }

    /**
     * Save custom fields.
     */
    public function perform_save_custom_fields()
    {
        $custom_fields = Inc\Mains\Functions\Request::get_parameter('fields');
        foreach (json_decode($custom_fields) as $custom_field) {
            switch ($custom_field->type) {
                case 'textarea':
                case 'text-content':
                case 'text-field':
                    do_action('wpml_register_single_string', 'bookme', 'custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label), $custom_field->label);
                    break;
                case 'checkboxes':
                case 'radio-buttons':
                case 'drop-down':
                    do_action('wpml_register_single_string', 'bookme', 'custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label), $custom_field->label);
                    foreach ($custom_field->items as $label) {
                        do_action('wpml_register_single_string', 'bookme', 'custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label) . '=' . sanitize_title($label), $label);
                    }
                    break;
            }
        }
        update_option('bookme_custom_fields', $custom_fields);
        update_option('bookme_custom_fields_per_service', (int)Inc\Mains\Functions\Request::get_parameter('bookme_custom_fields_per_service'));
        update_option('bookme_custom_fields_merge', (int)Inc\Mains\Functions\Request::get_parameter('bookme_custom_fields_merge'));
        wp_send_json_success();
    }
}