<?php

namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\DateTime;
use Bookme\Inc\Mains\Functions\Request;

/**
 * Class Staff
 */
class Employees extends Inc\Core\App
{

    const page_slug = 'bookme-staff';

    /**
     * execute page.
     */
    public function execute()
    {
        $assets = BOOKME_URL . 'assets/admin/';
        $public_assets = BOOKME_URL . 'assets/front/';

        if (get_option('bookme_phone_default_country') != 'disabled') {
            wp_enqueue_style('bookme-intlTelInput', $public_assets . 'css/intlTelInput.css', array(), BOOKME_VERSION);
            wp_enqueue_script('bookme-intlTelInput-js', $public_assets . 'js/intlTelInput.min.js', array('jquery'), BOOKME_VERSION);
        }

        Fragments::enqueue_global();

        wp_enqueue_media();
        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-multi-select', $assets . 'css/jquery.multiselect.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-side-panel-jquery', $assets . 'js/jquery-slidePanel.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-custom', $assets . 'js/custom.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-multi-select-js', $assets . 'js/jquery.multiselect.js', array(), BOOKME_VERSION);
        wp_enqueue_script('bookme-employee', $assets . 'js/pages/employee.js', array('jquery'), BOOKME_VERSION);

        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        wp_localize_script('bookme-employee', 'Bookme', array(
            'are_you_sure' => esc_html__('Are you sure?', 'bookme'),
            'saved' => esc_html__('Member has been saved.', 'bookme'),
            'repeat' => esc_html__('Repeat every year', 'bookme'),
            'we_are_not_working' => esc_html__('We are not working on this day', 'bookme'),
            'start_of_week' => (int)get_option('start_of_week'),
            'days' => array_values($wp_locale->weekday_abbrev),
            'months' => array_values($wp_locale->month),
            'intlTelInput' => array(
                'enabled' => get_option('bookme_phone_default_country') != 'disabled',
                'utils' => $public_assets . 'js/intlTelInput.utils.js',
                'country' => get_option('bookme_phone_default_country')
            ),
            'csrf_token' => Inc\Mains\Functions\System::get_security_token()
        ));

        $add_employee_panel_url = admin_url('admin-ajax.php?action=bookme_add_employee_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token());
        $edit_employee_panel_url = admin_url('admin-ajax.php?action=bookme_edit_employee_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token());
        $edit_holidays_panel_url = admin_url('admin-ajax.php?action=bookme_holidays_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token());

        // Check if this request is the request after google auth, set the token-data to the staff.
        if (Request::has_parameter('code')) {
            $google = new Inc\Mains\Google();
            $success_auth = $google->auth_code_handler(Request::get_parameter('code'));

            if ($success_auth) {
                $staff_id = base64_decode(strtr(Request::get_parameter('state'), '-_,', '+/='));
                $staff = new Inc\Mains\Tables\Employee();
                $staff->load($staff_id);
                $staff->set_google_data($google->get_access_token());
                $staff->save();

                exit ('<script>location.href="' . Inc\Mains\Google::generate_redirect_uri() . '&staff_id=' . $staff_id . '";</script>');
            } else {
                Inc\Mains\Functions\Session::set('staff_google_auth_error', json_encode($google->get_errors()));
            }
        }

        if (Request::has_parameter('google_logout')) {
            $active_staff_id = Request::get_parameter('google_logout');
            $staff = new Inc\Mains\Tables\Employee();
            if ($staff->load($active_staff_id) && $staff->get_google_data()) {
                $google = new Inc\Mains\Google();
                $google->load_by_staff($staff);
                $google->revoke_token();
            }
        }

        $employees = $this->get_employees();

        if ($file = get_option('bookme_secret_file')) {
            if (file_exists(BOOKME_PATH . '/templates/admin/employees/' . $file . '.php')) {
                Inc\Core\Template::create('employees/'.$file)->display(compact('employees', 'add_employee_panel_url', 'edit_employee_panel_url', 'edit_holidays_panel_url'));
                return;
            }
        }

        Inc\Core\Template::create('employees/page')->display();
    }

    /**
     * Add employee panel
     */
    public function perform_add_employee_panel()
    {
        $wp_users = $this->get_wp_users(Request::get_parameter('staff_id'));
        Inc\Core\Template::create('employees/add-employee-panel')->display(compact('wp_users'));
        wp_die();
    }

    /**
     * Create new employee
     */
    public function perform_add_employee()
    {
        $employee = new Inc\Mains\Tables\Employee(Request::get_post_parameters());
        $employee->save();

        if ($employee) {
            $edit_employee_panel_url = admin_url('admin-ajax.php?action=bookme_edit_employee_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token());
            $edit_holidays_panel_url = admin_url('admin-ajax.php?action=bookme_holidays_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token());
            wp_send_json_success(Inc\Core\Template::create('employees/employee-list')->display(array('member' => $employee->get_fields(), 'edit_employee_panel_url' => $edit_employee_panel_url, 'edit_holidays_panel_url' => $edit_holidays_panel_url), false));
        }
    }

    /**
     * Edit employee panel
     */
    public function perform_edit_employee_panel()
    {
        $gc_errors = array();
        $staff = new Inc\Mains\Tables\Employee();
        $staff->load(Request::get_parameter('id'));

        if ($g_errors = Inc\Mains\Functions\Session::get('employee_google_auth_error')) {
            foreach ((array)json_decode($g_errors, true) as $error) {
                $gc_errors[] = $error;
            }
            Inc\Mains\Functions\Session::destroy('employee_google_auth_error');
        }
        $google_calendars = array();
        $authUrl = null;
        if ($staff->get_google_data() == '') {
            if (get_option('bookme_gc_client_id') == '') {
                $authUrl = false;
            } else {
                $google = new Inc\Mains\Google();
                $authUrl = $google->create_auth_url($staff->get_id());
            }
        } else {
            $google = new Inc\Mains\Google();
            if ($google->load_by_staff($staff)) {
                $google_calendars = $google->get_calendar_list();
            } else {
                foreach ($google->get_errors() as $error) {
                    $gc_errors[] = $error;
                }
            }
        }

        /** @var \wpdb $wpdb */
        global $wpdb;

        $wp_users = Inc\Mains\Functions\System::is_current_user_admin() ? $this->get_wp_users($staff->get_id()) : array();

        // employee details
        $employee = $staff->get_fields();

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

        // employee services
        $employee_services = array();
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT service_id, price, capacity_min, capacity_max 
                    FROM `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` 
                    WHERE staff_id = %d",
                $staff->get_id()
            ),
            ARRAY_A
        );
        foreach ($data as $row) {
            $employee_services[$row['service_id']] = $row;
        }

        // schedule
        $employee_schedule = $staff->get_schedule();
        foreach ($employee_schedule as $key => $schedule) {
            $employee_schedule[$key]['breaks'] = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM `" . Inc\Mains\Tables\EmployeeScheduleBreak::get_table_name() . "`
                    WHERE staff_schedule_id = %d",
                    $schedule['id']
                ),
                ARRAY_A
            );
        }

        Inc\Core\Template::create('employees/edit-employee-panel')->display(compact('employee', 'all_services', 'employee_services', 'employee_schedule', 'wp_users', 'authUrl', 'google_calendars', 'gc_errors'));
        wp_die();
    }

    /**
     * Update employee
     */
    public function perform_update_employee()
    {
        if (!Inc\Mains\Functions\System::is_current_user_admin()) {
            // Check permissions to prevent one staff member from updating profile of another staff member.
            do {
                if (get_option('bookme_allow_staff_edit_profile')) {
                    $staff = new Inc\Mains\Tables\Employee();
                    $staff->load(Request::get_parameter('id'));
                    if ($staff->get_wp_user_id() == get_current_user_id()) {
                        unset ($_POST['wp_user_id']);
                        break;
                    }
                }
                do_action('admin_page_access_denied');
                wp_die('Bookme: ' . __('You do not have sufficient permissions to access this page.'));
            } while (0);
        }

        /** @var \wpdb $wpdb */
        global $wpdb;

        $employee = new Inc\Mains\Tables\Employee();
        $employee->load(Request::get_parameter('id'));
        $employee->set_fields(Request::get_post_parameters());

        // Verify google calendar.
        if (!empty($employee->get_google_calendar_id())) {
            $google = new Inc\Mains\Google();
            if (!$google->load_by_staff_id($employee->get_id()) || !$google->validate_calendar($employee->get_google_calendar_id())) {
                wp_send_json_error(array('error' => implode('<br>', $google->get_errors())));

            }
        }

        // save details
        $employee->save();

        // save services
        $wpdb->delete(
            Inc\Mains\Tables\EmployeeService::get_table_name(),
            array('staff_id' => $employee->get_id()),
            array('%d')
        );
        if (Request::has_parameter('services')) {
            foreach (Request::get_parameter('services') as $service_id) {
                $staff_service = new Inc\Mains\Tables\EmployeeService();
                $staff_service
                    ->set_capacity_min(Request::get_parameter("capacity_min")[ $service_id ])
                    ->set_capacity_max(Request::get_parameter("capacity_max")[ $service_id ])
                    ->set_price(Request::get_parameter("price")[ $service_id ])
                    ->set_service_id($service_id)
                    ->set_staff_id($employee->get_id())
                    ->save();
            }
        }

        // save schedule
        if (Request::has_parameter('days')) {
            foreach (Request::get_parameter('days') as $id => $day_index) {
                $res_schedule = new Inc\Mains\Tables\EmployeeSchedule();
                $res_schedule->load($id);
                $res_schedule->set_day_index($day_index);
                if (!empty(Request::get_parameter("start_time")[ $day_index ])) {
                    $res_schedule
                        ->set_start_time(Request::get_parameter("start_time")[ $day_index ])
                        ->set_end_time(Request::get_parameter("end_time")[$day_index]);
                } else {
                    $res_schedule
                        ->set_start_time(null)
                        ->set_end_time(null);
                }
                $res_schedule->save();
            }
        }

        wp_send_json_success();
    }

    /**
     * Perform all employees
     */
    public function perform_get_employees()
    {
        $staff_members = $this->get_employees();
        $edit_employee_panel_url = admin_url('admin-ajax.php?action=bookme_edit_employee_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token());
        $edit_holidays_panel_url = admin_url('admin-ajax.php?action=bookme_holidays_panel&csrf_token=' . Inc\Mains\Functions\System::get_security_token());
        $html = '';
        foreach ($staff_members as $staff) {
            $html .= Inc\Core\Template::create('employees/employee-list')->display(array('member' => $staff, 'edit_employee_panel_url' => $edit_employee_panel_url, 'edit_holidays_panel_url' => $edit_holidays_panel_url), false);
        }
        wp_send_json_success(array('html' => $html));
    }

    /**
     * Save / Update schedule break
     */
    public function perform_schedule_save_break()
    {
        $start_time = Request::get_parameter('start_time');
        $end_time = Request::get_parameter('end_time');
        $working_start = Request::get_parameter('working_start');
        $working_end = Request::get_parameter('working_end');

        if (DateTime::time_to_seconds($start_time) >= DateTime::time_to_seconds($end_time)) {
            wp_send_json_error(array('message' => esc_html__('The start time must be less than the end time', 'bookme'),));
        }

        $res_schedule = new Inc\Mains\Tables\EmployeeSchedule();
        $res_schedule->load(Request::get_parameter('staff_schedule_id'));

        $break_id = Request::get_parameter('break_id', 0);

        $in_working_time = $working_start <= $start_time && $start_time <= $working_end
            && $working_start <= $end_time && $end_time <= $working_end;
        if (!$in_working_time || !$res_schedule->is_break_available($start_time, $end_time, $break_id)) {
            wp_send_json_error(array('message' => esc_html__('This time period is not available', 'bookme'),));
        }

        $formatted_start = DateTime::format_time(DateTime::time_to_seconds($start_time));
        $formatted_end = DateTime::format_time(DateTime::time_to_seconds($end_time));
        $formatted_interval = $formatted_start . ' - ' . $formatted_end;

        if ($break_id) {
            $break = new Inc\Mains\Tables\EmployeeScheduleBreak();
            $break->load($break_id);
            $break->set_start_time($start_time)
                ->set_end_time($end_time)
                ->save();

            wp_send_json_success(array('interval' => $formatted_interval));
        } else {
            $res_schedule_break = new Inc\Mains\Tables\EmployeeScheduleBreak(Request::get_post_parameters());

            if ($res_schedule_break->save()) {
                wp_send_json(array(
                    'success' => true,
                    'content' => Inc\Core\Template::create('employees/break-item')->display(array('break' => $res_schedule_break->get_fields()), false)
                ));
            } else {
                wp_send_json_error(array('message' => esc_html__('Unexpected error, Please try again.', 'bookme'),));
            }
        }
    }

    /**
     * Delete break
     */
    public function perform_delete_schedule_break()
    {
        $break = new Inc\Mains\Tables\EmployeeScheduleBreak();
        $break->set_id(Request::get_parameter('id', 0));
        $break->delete();

        wp_send_json_success();
    }

    /**
     * Update employee position
     */
    public function perform_update_employee_position()
    {
        $emp_sorts = Request::get_parameter('position');
        foreach ($emp_sorts as $position => $id) {
            $emp_sort = new Inc\Mains\Tables\Employee();
            $emp_sort->load($id);
            $emp_sort->set_position($position);
            $emp_sort->save();
        }
    }

    /**
     * Delete employees
     */
    public function perform_delete_employees()
    {
        if (Inc\Mains\Functions\System::is_current_user_admin()) {
            $ids = Request::get_parameter('ids', array());
            if (is_array($ids) && !empty ($ids)) {
                foreach ($ids as $id) {
                    if ($staff = Inc\Mains\Tables\Employee::find($id)) {
                        $staff->delete();
                    }
                }
            }
        }

        wp_send_json_success();
    }

    /**
     * Delete employees
     */
    public function perform_holidays_panel()
    {
        Inc\Core\Template::create('employees/holidays-panel')->display(array('id' => Request::get_parameter('id', 0), 'holidays' => $this->get_holidays(Request::get_parameter('id', 0))));
        wp_die();
    }

    /**
     * Update days off
     */
    public function perform_holidays_update()
    {
        global $wpdb;

        $id = Request::get_parameter('id');
        $holiday = Request::get_parameter('holiday') == 'true';
        $repeat = (int) (Request::get_parameter('repeat') == 'true');
        $day = Request::get_parameter('day', false);
        $staff_id = Request::get_parameter('staff_id');
        if ($staff_id) {
            if ($id) {
                if ($holiday) {
                    $wpdb->update(
                        Inc\Mains\Tables\Holiday::get_table_name(),
                        array('repeat_event' => $repeat),
                        array('id' => $id),
                        array('%d')
                    );
                } else {
                    $wpdb->delete(
                        Inc\Mains\Tables\Holiday::get_table_name(),
                        array('id' => $id),
                        array('%d')
                    );
                }
            } elseif ($holiday && $day) {
                list ($d, $m, $Y) = explode('-', $day);
                $wpdb->insert(
                    Inc\Mains\Tables\Holiday::get_table_name(),
                    array('date' => $Y.'-'.$m.'-'.$d, 'repeat_event' => $repeat, 'staff_id' => $staff_id),
                    array('%s', '%d', '%d')
                );
            }
            // And return refreshed events.
            echo json_encode($this->get_holidays($staff_id));
        }
        exit;
    }

    private function get_holidays($staff_id)
    {
        global $wpdb;
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `" . Inc\Mains\Tables\Holiday::get_table_name() . "` 
                    WHERE staff_id = %d",
                $staff_id),
            ARRAY_A);
        $holidays = array();
        foreach ($data as $holiday) {
            list ($Y, $m, $d) = explode('-', $holiday['date']);
            $holidays[$holiday['id']] = array(
                'm' => (int)$m,
                'd' => (int)$d,
            );
            // if not repeated holiday, add the year
            if (!$holiday['repeat_event']) {
                $holidays[$holiday['id']]['y'] = (int)$Y;
            }
        }

        return $holidays;
    }

    /**
     * Get all employees
     * @return array
     */
    private function get_employees()
    {
        global $wpdb;
        if (Inc\Mains\Functions\System::is_current_user_admin()) {
            $employees = $wpdb->get_results("SELECT e.*, GROUP_CONCAT(s.title) services 
            FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` e
            LEFT JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` ss ON ss.staff_id = e.id
            LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` s ON ss.service_id = s.id
            GROUP BY e.id
            ORDER BY position", ARRAY_A);
        } else {
            $employees = $wpdb->get_results($wpdb->prepare(
                "SELECT e.*, GROUP_CONCAT(s.title) services FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` e 
                LEFT JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` ss ON ss.staff_id = e.id
                LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` s ON ss.service_id = s.id
                WHERE wp_user_id = %d
                GROUP BY e.id",
                get_current_user_id()),
                ARRAY_A);
        }

        return $employees;
    }

    /**
     * Get wp users for staff member
     * @param int|null $staff_id
     * @return array
     */
    private function get_wp_users($staff_id = null)
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        if (!is_multisite()) {
            $query = sprintf(
                'SELECT ID, user_email, display_name FROM ' . $wpdb->users . '
               WHERE ID NOT IN(SELECT DISTINCT IFNULL( wp_user_id, 0 ) FROM ' . Inc\Mains\Tables\Employee::get_table_name() . ' %s)
               ORDER BY display_name',
                $staff_id !== null
                    ? 'WHERE ' . Inc\Mains\Tables\Employee::get_table_name() . '.id <> ' . (int)$staff_id
                    : ''
            );
            $users = $wpdb->get_results($query, ARRAY_A);
        } else {
            // In Multisite show users only for current blog.
            $query = sprintf(
                "SELECT DISTINCT wp_user_id FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "`
               WHERE wp_user_id IS NOT NULL %s",
                $staff_id !== null
                    ? 'AND id <> ' . (int)$staff_id
                    : ''
            );

            $exclude_wp_users = array();
            foreach ($wpdb->get_results($query, ARRAY_A) as $staff) {
                $exclude_wp_users[] = $staff['wp_user_id'];
            }
            $users = array_map(
                function (\WP_User $wp_user) {
                    $arr = array();
                    $arr['ID'] = $wp_user->ID;
                    $arr['user_email'] = $wp_user->data->user_email;
                    $arr['display_name'] = $wp_user->data->display_name;

                    return $arr;
                },
                get_users(array('blog_id' => get_current_blog_id(), 'orderby' => 'display_name', 'exclude' => $exclude_wp_users))
            );
        }

        return $users;
    }

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {
        // set permissions for user
        $permissions = array(
            'perform_edit_employee_panel' => 'user',
            'perform_update_employee' => 'user',
            'perform_holidays_panel' => 'user',
            'perform_holidays_update' => 'user',
            'perform_schedule_save_break' => 'user',
            'perform_delete_schedule_break' => 'user'
        );
        Inc\Core\Ajax::register_ajax_actions($this,$permissions);
    }
}