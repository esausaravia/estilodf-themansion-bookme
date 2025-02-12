<?php
namespace Bookme\App\Front;

use Bookme\Inc;

/**
 * Class Mollie for Payment Gateway
 */
class Mollie extends Inc\Core\App
{

    public function checkout()
    {
        $form_id  = Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' );
        $userData = new Inc\Mains\Booking\UserData( $form_id );
        if ( $userData->load() ) {
            Inc\Payment\Mollie::payment_page( $form_id, $userData, Inc\Mains\Functions\Request::get_parameter( 'response_url' ) );
        }
    }

    /**
     * Redirect from Payment Form to Bookme page
     */
    public function response()
    {
        global $wpdb;
        $form_id  = Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' );
        $userData = new Inc\Mains\Booking\UserData( $form_id );
        $userData->load();
        if ( $payment = Inc\Mains\Functions\Session::getFormVar( $form_id, 'payment' ) ) {
            if ( $payment['status'] == 'pending' ) {
                $mollie_payment = Inc\Payment\Mollie::get_payment( $payment['data'] );
                if ( $mollie_payment->isOpen() || $mollie_payment->isPending() || $mollie_payment->isPaid() ) {
                    // Payment processing
                    $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_MOLLIE, 'processing' );
                    @wp_redirect( remove_query_arg( Inc\Payment\Mollie::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
                } else {
                    // Customer cancel payment
                    $data = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM `".Inc\Mains\Tables\CustomerBooking::get_table_name()."` WHERE `payment_id` = %d",
                            $mollie_payment->metadata->payment_id
                        ),
                        ARRAY_A
                    );
                    $data = Inc\Mains\Functions\System::bind_data_with_table( Inc\Mains\Tables\CustomerBooking::class,$data);
                    /** @var Inc\Mains\Tables\CustomerBooking $ca */
                    foreach ( $data as $ca ) {
                        $ca->delete_cascade();
                    }
                    $wpdb->delete(
                        Inc\Mains\Tables\Payment::get_table_name(),
                        array(
                            'type' => Inc\Mains\Tables\Payment::TYPE_MOLLIE,
                            'id' => $mollie_payment->metadata->payment_id
                        ),
                        array('%s', '%d')
                    );
                    $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_MOLLIE, 'cancelled' );

                    @wp_redirect( remove_query_arg( Inc\Payment\Mollie::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
                }
            }
        }
        exit;
    }
}