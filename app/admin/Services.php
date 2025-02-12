<?php

namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\Request;

/**
 * Class Services
 */
class Services extends Inc\Core\App
{

    const page_slug = 'bookme-services';

    /**
     * execute page.
     */
    public function execute()
    {
        Fragments::enqueue_global();
        $assets = BOOKME_URL . 'assets/admin/';

        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-color-picker', $assets . 'css/color-picker.min.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-multi-select', $assets . 'css/jquery.multiselect.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-side-panel-jquery', $assets . 'js/jquery-slidePanel.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-custom', $assets . 'js/custom.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-color-picker-js', $assets . 'js/color-picker.es5.min.js', array(), BOOKME_VERSION);
        wp_enqueue_script('bookme-multi-select-js', $assets . 'js/jquery.multiselect.js', array(), BOOKME_VERSION);
        wp_enqueue_script('bookme-service', $assets . 'js/pages/service.js', array('jquery', 'jquery-ui-sortable'), BOOKME_VERSION);

        global $wpdb;
        $employees_array = $wpdb->get_results("SELECT * FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` ORDER BY position", ARRAY_A);
        $employees = array();
        foreach ($employees_array as $employee) {
            $employees[$employee['id']] = $employee['full_name'];
        }

        wp_localize_script('bookme-service', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'are_you_sure' => esc_html__('Are you sure?', 'bookme'),
            'category_updated' => esc_html__('Category has been saved.', 'bookme'),
            'service_updated' => esc_html__('Settings has been saved.', 'bookme'),
            'reorder' => esc_attr__('Reorder', 'bookme'),
            'employees' => $employees
        ));

        $categories = $wpdb->get_results("SELECT * FROM `" . Inc\Mains\Tables\Category::get_table_name() . "` ORDER BY position", ARRAY_A);
        $services = $this->get_services();

        $service_panel_url = admin_url('admin-ajax.php?action=bookme_service_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token());
        Inc\Core\Template::create('services/page')->display(compact('categories','services', 'service_panel_url'));
    }

    /**
     *  Create category
     */
    public function perform_create_category()
    {
        $html = '';
        $category = new Inc\Mains\Tables\Category();
        $category->set_name(__('New Category', 'bookme'));
        if ($category->save()) {
            $html = Inc\Core\Template::create('services/category-item')->display(array('category' => $category->get_fields()), false);
        }
        wp_send_json_success(compact('html'));
    }

    /**
     *  Update category
     */
    public function perform_update_category()
    {
        $category = new Inc\Mains\Tables\Category(Request::get_post_parameters());
        $category->save();
    }

    /**
     *  Delete category
     */
    public function perform_delete_category()
    {
        $category = new Inc\Mains\Tables\Category(Request::get_post_parameters());
        $category->delete();
    }

    /**
     *  Update category position
     */
    public function perform_category_position()
    {
        $category_sorts = Request::get_parameter('position');
        foreach ($category_sorts as $position => $category_id) {
            $category_sort = new Inc\Mains\Tables\Category();
            $category_sort->load($category_id);
            $category_sort->set_position($position);
            $category_sort->save();
        }
    }

    /**
     * Get all services of a category
     */
    public function perform_get_category_services()
    {
        $category_id = Request::get_parameter('category_id', 0);
        wp_send_json_success(Inc\Core\Template::create('services/services-list')->display( array(
            'services' => $this->get_services($category_id),
            'service_panel_url' => admin_url('admin-ajax.php?action=bookme_service_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token())
        ), false));
    }

    /**
     * Service panel
     */
    public function perform_service_panel()
    {
        global $wpdb;
        $service = array();
        $categories = $wpdb->get_results("SELECT * FROM `" . Inc\Mains\Tables\Category::get_table_name() . "` ORDER BY position", ARRAY_A);
        $employees = $wpdb->get_results("SELECT * FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` ORDER BY position", ARRAY_A);
        $employee_preference = array(
            Inc\Mains\Tables\Service::PREFERRED_ORDER => __('Specified order', 'bookme'),
            Inc\Mains\Tables\Service::PREFERRED_LEAST_OCCUPIED => __('Least occupied that day', 'bookme'),
            Inc\Mains\Tables\Service::PREFERRED_MOST_OCCUPIED => __('Most occupied that day', 'bookme'),
            Inc\Mains\Tables\Service::PREFERRED_LEAST_EXPENSIVE => __('Least expensive', 'bookme'),
            Inc\Mains\Tables\Service::PREFERRED_MOST_EXPENSIVE => __('Most expensive', 'bookme'),
        );
        if (Request::has_parameter('service_id')) {
            $data = $this->get_service_data(Request::get_parameter('service_id'));
            if ($data = reset($data))
                $service = $data;
        }

        Inc\Core\Template::create('services/service-panel')->display(compact('categories', 'service', 'employee_preference', 'employees'));
        wp_die();
    }

    /**
     * Add/Update Service
     */
    public function perform_update_service()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        // save service
        $service = new Inc\Mains\Tables\Service();

        if(Request::get_parameter('id')) {
            $service->load(Request::get_parameter('id'));
        }
        $service->set_fields(Request::get_post_parameters());

        if(!$service->get_category_id())
            $service->set_category_id(null);

        if(empty($service->get_color()))
            $service->set_color(sprintf( '#%06X', mt_rand( 0, 0x64FFFF ) ));

        if($service->get_limit_period() == 'off' || !$service->get_bookings_limit())
            $service->set_bookings_limit(null);

        $service->save();
        $service_id = $service->get_id();

        // save employee preference
        $staff_preferences_data = $wpdb->get_results($wpdb->prepare("SELECT * 
                    FROM `" . Inc\Mains\Tables\EmployeePreferenceOrder::get_table_name() . "` 
                    WHERE service_id = %d", $service_id),
            ARRAY_A);
        /* @var Inc\Mains\Tables\EmployeePreferenceOrder[] $staff_preferences */
        $staff_preferences = Inc\Mains\Functions\System::bind_data_with_table(Inc\Mains\Tables\EmployeePreferenceOrder::class,$staff_preferences_data,'staff_id');

        foreach ((array) Request::get_parameter('positions') as $position => $staff_id ) {
            if ( array_key_exists( $staff_id, $staff_preferences ) ) {
                $staff_preferences[ $staff_id ]->set_position( $position )->save();
            } else {
                $preference = new Inc\Mains\Tables\EmployeePreferenceOrder();
                $preference
                    ->set_service_id( $service->get_id() )
                    ->set_staff_id( $staff_id )
                    ->set_position( $position )
                    ->save();
            }
        }

        $staff_ids = Request::get_parameter('staff_ids', array());
        if (empty ($staff_ids)) {
            $wpdb->delete(
                Inc\Mains\Tables\EmployeeService::get_table_name(),
                array(
                    'service_id' => $service_id
                ),
                array('%d')
            );
        } else {
            $wpdb->query( $wpdb->prepare("DELETE FROM `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "`
                WHERE service_id = %d 
                AND staff_id NOT IN (".implode(',', $staff_ids).")",
                $service_id) );
            if (Request::get_parameter('update_staff', false)) {
                $wpdb->update(
                    Inc\Mains\Tables\EmployeeService::get_table_name(),
                    array(
                        'price' => $service->get_price(),
                        'capacity_min' => $service->get_capacity_min(),
                        'capacity_max' => $service->get_capacity_max(),
                    ),
                    array('service_id' => Request::get_parameter('id'))
                );
            }

            // Create records for newly linked staff.
            $existing_staff_ids = array();
            $res = $wpdb->get_results($wpdb->prepare("SELECT staff_id 
                    FROM `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` 
                    WHERE service_id = %d", $service_id),
                ARRAY_A);
            foreach ($res as $staff) {
                $existing_staff_ids[] = $staff['staff_id'];
            }
            foreach ($staff_ids as $staff_id) {
                if (!in_array($staff_id, $existing_staff_ids)) {
                    $staff_service = new Inc\Mains\Tables\EmployeeService();
                    $staff_service->set_staff_id($staff_id)
                        ->set_service_id($service_id)
                        ->set_price($service->get_price())
                        ->set_capacity_min($service->get_capacity_min())
                        ->set_capacity_max($service->get_capacity_max())
                        ->save();
                }
            }
        }

        wp_send_json_success(compact( 'service_id'));
    }

    /**
     * Update services position.
     */
    public function perform_update_services_position()
    {
        $services_sorts = Request::get_parameter('position');
        foreach ($services_sorts as $position => $service_ids) {
            $services_sort = new Inc\Mains\Tables\Service();
            $services_sort->load($service_ids);
            $services_sort->set_position($position);
            $services_sort->save();
        }
    }

    /**
     * Delete services
     */
    public function perform_delete_services()
    {
        $service_ids = Request::get_parameter('service_ids', array());
        if (is_array($service_ids) && !empty ($service_ids)) {
            global $wpdb;
            $wpdb->query( "DELETE FROM `" . Inc\Mains\Tables\Service::get_table_name() . "`
                WHERE id IN (".implode(',', $service_ids).")" );
        }
        wp_send_json_success();
    }

    /**
     * Update service employees preference order
     */
    public function perform_update_service_employee_preference_orders()
    {
        $service_id = Request::get_parameter('service_id');
        $positions = (array) Request::get_parameter('positions');

        global $wpdb;
        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * 
                    FROM `" . Inc\Mains\Tables\EmployeePreferenceOrder::get_table_name() . "` 
                    WHERE service_id = %d",
                $service_id),
            ARRAY_A);

        /** @var Inc\Mains\Tables\EmployeePreferenceOrder[] $staff_preferences */
        $staff_preferences = Inc\Mains\Functions\System::bind_data_with_table(Inc\Mains\Tables\EmployeePreferenceOrder::class, $res, 'staff_id');

        foreach ($positions as $position => $staff_id) {
            if (array_key_exists($staff_id, $staff_preferences)) {
                $staff_preferences[$staff_id]->set_position($position)->save();
            } else {
                $preference = new Inc\Mains\Tables\EmployeePreferenceOrder();
                $preference
                    ->set_service_id($service_id)
                    ->set_staff_id($staff_id)
                    ->set_position($position)
                    ->save();
            }
        }

        wp_send_json_success();
    }

    /**
     * @param int $category_id
     * @return array
     */
    private function get_services($category_id = 0)
    {
        global $wpdb;
        $services = $wpdb->get_results($wpdb->prepare("SELECT 
                    s.*, 
                    COUNT(staff.id) AS total_staff, 
                    GROUP_CONCAT(DISTINCT staff.id) AS staff_ids, 
                    GROUP_CONCAT(DISTINCT sp.staff_id ORDER BY sp.position ASC) AS pref_staff_ids 
                FROM `" . Inc\Mains\Tables\Service::get_table_name() . "` s 
                LEFT JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` ss ON ss.service_id = s.id
                LEFT JOIN `" . Inc\Mains\Tables\EmployeePreferenceOrder::get_table_name() . "` sp ON sp.service_id = s.id
                LEFT JOIN `" . Inc\Mains\Tables\Employee::get_table_name() . "` staff ON staff.id = ss.staff_id
                WHERE s.category_id = %d OR !%d
                GROUP BY s.id
                ORDER BY s.position",
                array($category_id, $category_id)),
            ARRAY_A);

        return $services;
    }

    /**
     * Get service data by id
     * @param $id int
     * @return array
     */
    private function get_service_data($id = 0)
    {
        global $wpdb;
        $services = $wpdb->get_results($wpdb->prepare("SELECT 
                    s.*, 
                    COUNT(staff.id) AS total_staff, 
                    GROUP_CONCAT(DISTINCT staff.id) AS staff_ids, 
                    GROUP_CONCAT(DISTINCT sp.staff_id ORDER BY sp.position ASC) AS pref_staff_ids 
                FROM `" . Inc\Mains\Tables\Service::get_table_name() . "` s 
                LEFT JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` ss ON ss.service_id = s.id
                LEFT JOIN `" . Inc\Mains\Tables\EmployeePreferenceOrder::get_table_name() . "` sp ON sp.service_id = s.id
                LEFT JOIN `" . Inc\Mains\Tables\Employee::get_table_name() . "` staff ON staff.id = ss.staff_id
                WHERE s.id = %d
                GROUP BY s.id
                ORDER BY s.position", $id),
            ARRAY_A);

        return $services;
    }
}