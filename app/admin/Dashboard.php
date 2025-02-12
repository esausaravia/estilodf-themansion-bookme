<?php
namespace Bookme\App\Admin;

use Bookme\Inc;
/**
 * Class Dashboard
 */
class Dashboard extends Inc\Core\App {

    const page_slug = 'bookme-dashboard';

    /**
     * execute page
     */
    public function execute()
    {
        $assets = BOOKME_URL.'assets/admin/';

        wp_enqueue_style('bookme-datatables', $assets . 'css/datatables.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-daterangepicker', $assets . 'css/daterangepicker.css', array(), BOOKME_VERSION);
        Fragments::enqueue_global();

        wp_enqueue_script('bookme-moment-js', $assets . 'js/moment.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-daterangepicker-js', $assets . 'js/daterangepicker.js', array('jquery', 'bookme-moment-js'), BOOKME_VERSION);
        wp_enqueue_script('bookme-datatables', $assets . 'js/jquery.dataTables.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-chart-js', $assets . 'js/chart.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-dashboard', $assets . 'js/pages/dashboard.js', array('jquery'), BOOKME_VERSION);

        global $wp_locale;

        $currencies = Inc\Mains\Functions\Price::get_currencies();

        wp_localize_script('bookme-dashboard', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'zeroRecords' => esc_html__('No payments available.', 'bookme'),
            'processing' => esc_html__('Processing...', 'bookme'),
            'are_you_sure' => esc_html__('Are you sure?', 'bookme'),
            'tomorrow' => esc_html__('Tomorrow', 'bookme'),
            'today' => esc_html__('Today', 'bookme'),
            'yesterday' => esc_html__('Yesterday', 'bookme'),
            'last_7' => esc_html__('Last 7 Days', 'bookme'),
            'last_30' => esc_html__('Last 30 Days', 'bookme'),
            'this_month' => esc_html__('This Month', 'bookme'),
            'last_month' => esc_html__('Last Month', 'bookme'),
            'custom_range' => esc_html__('Custom Range', 'bookme'),
            'apply' => esc_html__('Apply', 'bookme'),
            'cancel' => esc_html__('Cancel', 'bookme'),
            'to' => esc_html__('To', 'bookme'),
            'from' => esc_html__('From', 'bookme'),
            'no_result_found' => esc_html__('No result found.', 'bookme'),
            'bookings' => esc_html__( 'Bookings', 'bookme' ),
            'revenue'      => esc_html__( 'Revenue', 'bookme' ),
            'calendar' => array(
                'longMonths' => array_values($wp_locale->month),
                'shortMonths' => array_values($wp_locale->month_abbrev),
                'longDays' => array_values($wp_locale->weekday),
                'shortDays' => array_values($wp_locale->weekday_abbrev),
            ),
            'mjsDateFormat' => Inc\Mains\Functions\DateTime::convert_format('date', Inc\Mains\Functions\DateTime::FORMAT_MOMENT_JS),
            'startOfWeek' => (int)get_option('start_of_week'),
            'currency'     => $currencies[ get_option( 'bookme_currency' ) ]['symbol'],
        ));

        Inc\Core\Template::create('dashboard/page')->display();
    }


    /**
     * Get data for dashboard
     */
    public function perform_get_data_for_dashboard()
    {
        list ( $start, $end ) = explode( ' - ', Inc\Mains\Functions\Request::get_parameter( 'range' ) );
        $start = date_create( $start );
        $end   = date_create( $end );
        $day   = array(
            'total'   => 0,
            'revenue' => 0,
        );
        $data  = array(
            'approved' => 0,
            'pending'  => 0,
            'total'    => 0,
            'revenue'  => 0,
            'days'   => array(),
            'labels' => array(),
        );
        $end->modify( '+1 day' );
        $period = new \DatePeriod( $start, \DateInterval::createFromDateString( '1 day' ), $end );
        /** @var \DateTime $dt */
        foreach ( $period as $dt ) {
            $data['labels'][] = date_i18n( 'M j', $dt->getTimestamp() );
            $data['days'][ $dt->format( 'Y-m-d' ) ] = $day;
        }

        global $wpdb;
        $records = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(ca.created) AS created, COUNT(1) AS quantity, p.paid AS revenue, ca.status, p.id
                FROM ".Inc\Mains\Tables\CustomerBooking::get_table_name()." ca 
                LEFT JOIN ".Inc\Mains\Tables\Payment::get_table_name()." p ON p.id = ca.payment_id
                WHERE ca.created BETWEEN %s AND %s
                GROUP BY DATE(ca.created), p.id, ca.status",
                $start->format( 'Y-m-d' ),
                $end->format( 'Y-m-d' )
            ),
            ARRAY_A
        );

        $payment_ids = array();
        foreach ( $records as $record ) {
            $created  = $record['created'];
            $quantity = $record['quantity'];
            $status   = $record['status'];
            if ( in_array( $record['id'], $payment_ids ) ) {
                $revenue = 0;
            } else {
                $payment_ids[] = $record['id'];
                $revenue       = $record['revenue'];
            }
            if ( array_key_exists( $status, $data ) ) {
                $data[ $status ] += $quantity;
            }
            $data['total']   += $quantity;
            $data['revenue'] += $revenue;
            $data['days'][ $created ]['total']   += $quantity;
            $data['days'][ $created ]['revenue'] += $revenue;
        }
        $data['revenue'] = Inc\Mains\Functions\Price::format( $data['revenue'] );

        wp_send_json_success( $data );
    }
}