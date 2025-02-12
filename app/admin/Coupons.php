<?php

namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\Request;

/**
 * Class Coupons
 */
class Coupons extends Inc\Core\App
{

    const page_slug = 'bookme-coupons';

    /**
     * execute page.
     */
    public function execute()
    {
        $assets = BOOKME_URL . 'assets/admin/';

        wp_enqueue_style('bookme-datatable', $assets . 'css/datatables.css', array(), BOOKME_VERSION);

        Fragments::enqueue_global();
        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-multi-select', $assets . 'css/jquery.multiselect.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-datatable-js', $assets . 'js/jquery.dataTables.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-side-panel-js', $assets . 'js/sidePanel.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-multi-select-js', $assets . 'js/jquery.multiselect.js', array(), BOOKME_VERSION);
        wp_enqueue_script('bookme-coupon', $assets . 'js/pages/coupons.js', array('jquery'), BOOKME_VERSION);

        /** @var \wpdb $wpdb */
        global $wpdb;
        // all services
        $all_services = $collection = array();
        $data = $wpdb->get_results(
            "SELECT c.name AS category_name, s.* 
                FROM `" . Inc\Mains\Tables\Category::get_table_name() . "` c
                INNER JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` s ON s.category_id = c.id
                ORDER BY c.position, s.position",
            ARRAY_A
        );
        foreach ($data as $row) {
            $all_services[$row['category_name']][] = $row;
            $collection[$row['id']] = $row;
        }

        wp_localize_script('bookme-coupon', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'edit' => esc_attr__('Edit', 'bookme'),
            'zeroRecords' => esc_attr__('No coupons available.', 'bookme'),
            'processing' => esc_attr__('Processing...', 'bookme'),
            'are_you_sure' => esc_attr__('Are you sure?', 'bookme'),
            'all_selected' => esc_attr__('All Services', 'bookme'),
            'nothing_selected' => esc_attr__('No services assigned', 'bookme'),
            'services' => $all_services,
            'collection' => $collection
        ));

        Inc\Core\Template::create('coupons/page')->display();
    }

    /**
     * Get coupons list
     */
    public function perform_get_coupons()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $coupons = $wpdb->get_results(
            "SELECT c.*, GROUP_CONCAT(DISTINCT s.id) AS service_ids 
                    FROM `" . Inc\Mains\Tables\Coupon::get_table_name() . "` c
                    LEFT JOIN `" . Inc\Mains\Tables\CouponService::get_table_name() . "` cs ON cs.coupon_id = c.id
                    LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` s ON s.id = cs.service_id
                    GROUP BY c.id",
            ARRAY_A);
        foreach ($coupons as &$coupon) {
            $coupon['service_ids'] = $coupon['service_ids'] ? explode(',', $coupon['service_ids']) : array();
        }

        wp_send_json_success($coupons);
    }

    /**
     * Create/update coupon
     */
    public function perform_save_coupon()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $coupon = new Inc\Mains\Tables\Coupon();
        $coupon->load(Request::get_parameter('id', 0));
        $coupon->set_fields(Request::get_post_parameters());
        $data = $coupon->get_fields();

        if ($data['discount'] < 0 || $data['discount'] > 100) {
            wp_send_json_error(array('message' => esc_html__('Discount should be between 0 and 100.', 'bookme')));
        } elseif ($data['deduction'] < 0) {
            wp_send_json_error(array('message' => esc_html__('Deduction should be a positive number.', 'bookme')));
        } else {
            $coupon->save();
            $service_ids = (array)Request::get_parameter('service_ids');
            if (empty($service_ids)) {
                $wpdb->delete(
                    Inc\Mains\Tables\CouponService::get_table_name(),
                    array(
                        'coupon_id' => $coupon->get_id()
                    ),
                    array('%d')
                );
            } else {
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM `" . Inc\Mains\Tables\CouponService::get_table_name() . "`
                        WHERE coupon_id = %d 
                        AND service_id NOT IN (" . implode(',', $service_ids) . ")",
                        $coupon->get_id()
                    )
                );

                $service_exists = array();
                $res = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT service_id 
                            FROM `" . Inc\Mains\Tables\CouponService::get_table_name() . "` 
                            WHERE coupon_id = %d",
                        $coupon->get_id()
                    ),
                    ARRAY_A);
                foreach ($res as $service) {
                    $service_exists[] = $service['service_id'];
                }

                foreach ($service_ids as $service_id) {
                    if (!in_array($service_id, $service_exists)) {
                        $coupon_service = new Inc\Mains\Tables\CouponService();
                        $coupon_service
                            ->set_coupon_id($coupon->get_id())
                            ->set_service_id($service_id)
                            ->save();
                    }
                }
            }

            $data = $coupon->get_fields();
            $data['service_ids'] = $service_ids;
            wp_send_json_success($data);
        }
    }

    /**
     * Delete coupons.
     */
    public function perform_delete_coupons()
    {
        global $wpdb;
        $coupon_ids = array_map('intval', Request::get_parameter('ids', array()));
        $wpdb->query("DELETE FROM `" . Inc\Mains\Tables\Coupon::get_table_name() . "` WHERE id IN (" . implode(',', $coupon_ids) . ")");
        wp_send_json_success();
    }
}