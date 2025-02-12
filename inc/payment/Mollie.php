<?php
namespace Bookme\Inc\Payment;

use Bookme\Inc;

/**
 * Class Mollie
 */
class Mollie
{
    // Array for cleaning Mollie request
    public static $remove_parameters = array( 'bookme_action', 'bookme_fid', 'error_msg' );

    public static function render_form($form_id, $page_url )
    {
        $userData = new Inc\Mains\Booking\UserData( $form_id );
        if ( $userData->load() ) {
            $replacement = array(
                '%form_id%' => $form_id,
                '%gateway%' => Inc\Mains\Tables\Payment::TYPE_MOLLIE,
                '%response_url%' => esc_attr( $page_url ),
            );
            $form = '<form method="post" class="bookme-%gateway%-form">
                <input type="hidden" name="bookme_fid" value="%form_id%"/>
                <input type="hidden" name="bookme_action" value="mollie-checkout"/>
                <input type="hidden" name="response_url" value="%response_url%"/>
             </form>';
            echo strtr( $form, $replacement );
        }
    }

    /**
     * Handles IPN messages
     */
    public static function ipn()
    {
        $payment_details = self::get_api()->payments->get( $_REQUEST['id'] );
        Mollie::handle_payment( $payment_details );
    }

    /**
     * Check gateway data and if ok save payment info
     *
     * @param \Mollie_API_Object_Payment $details
     */
    public static function handle_payment(\Mollie_API_Object_Payment $details )
    {
        global $wpdb;
        /** @var Inc\Mains\Tables\Payment $payment */
        // Customer cancel payment
        $data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `".Inc\Mains\Tables\Payment::get_table_name()."` 
                WHERE `id` = %d AND `type` = %s",
                $details->metadata->payment_id,
                Inc\Mains\Tables\Payment::TYPE_MOLLIE
            ),
            ARRAY_A
        );
        $payment = new Inc\Mains\Tables\Payment($data);

        if ( $details->isPaid() ) {
            // Handle completed card & bank transfers here
            $total    = (float) $payment->get_paid();
            $received = (float) $details->amount;

            if ( $payment->get_status() == Inc\Mains\Tables\Payment::STATUS_COMPLETED
                 || $received != $total
            ) {
                wp_send_json_success();
            } else {
                $payment->set_status( Inc\Mains\Tables\Payment::STATUS_COMPLETED )->save();
                if ( $order = Inc\Mains\Booking\DataHolders\Order::create_from_payment( $payment ) ) {
                    Inc\Mains\Notification\Sender::send_from_cart( $order );
                }
            }
        } elseif ( ! $details->isOpen() && ! $details->isPending() ) {
            /** @var Inc\Mains\Tables\CustomerBooking $ca */
            $data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM `".Inc\Mains\Tables\CustomerBooking::get_table_name()."` WHERE `payment_id` = %d",
                    $details->metadata->payment_id
                ),
                ARRAY_A
            );
            $data = Inc\Mains\Functions\System::bind_data_with_table( Inc\Mains\Tables\CustomerBooking::class,$data);
            foreach ( $data as $ca ) {
                $ca->delete_cascade();
            }
            $payment->delete();
        }
        wp_send_json_success();
    }

    /**
     * Redirect to Mollie Payment page, or step payment.
     *
     * @param $form_id
     * @param Inc\Mains\Booking\UserData $userData
     * @param string $page_url
     */
    public static function payment_page($form_id, Inc\Mains\Booking\UserData $userData, $page_url )
    {
        if ( get_option( 'bookme_currency' ) != 'EUR' ) {
            $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_MOLLIE, 'error', esc_html__( 'Mollie accepts payments in Euro only.', 'bookme' ) );
            @wp_redirect( remove_query_arg( self::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
            exit;
        }
        list( $total, $deposit ) = $userData->cart->get_info();
        $coupon  = $userData->get_coupon();
        $payment = new Inc\Mains\Tables\Payment();
        $payment->set_type( Inc\Mains\Tables\Payment::TYPE_MOLLIE )
            ->set_status( Inc\Mains\Tables\Payment::STATUS_PENDING )
            ->set_created( current_time( 'mysql' ) )
            ->set_total( $total )
            ->set_paid( $deposit )
            ->save();
        $order = $userData->save( $payment );
        try {
            $api = self::get_api();
            $mollie_payment = $api->payments->create( array(
                'amount'      => $deposit,
                'description' => $userData->cart->get_items_title( 125 ),
                'redirectUrl' => add_query_arg( array( 'bookme_action' => 'mollie-response', 'bookme_fid' => $form_id ), $page_url ),
                'webhookUrl'  => add_query_arg( array( 'bookme_action' => 'mollie-ipn' ), $page_url ),
                'metadata'    => array( 'payment_id' => $payment->get_id() ),
                'issuer'      => null
            ) );
            if ( $mollie_payment->isOpen() ) {
                if ( $coupon ) {
                    $coupon->claim();
                    $coupon->save();
                }
                $payment->set_details( $order, $coupon )->save();
                $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_MOLLIE, 'pending', $mollie_payment->id );
                header( 'Location: ' . $mollie_payment->getPaymentUrl() );
                exit;
            } else {
                self::delete_bookings( $order );
                $payment->delete();
                self::redirect_to( $userData, 'error', __( 'Mollie error.', 'bookme' ) );
            }
        } catch ( \Exception $e ) {
            self::delete_bookings( $order );
            $payment->delete();
            $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_MOLLIE, 'error', $e->getMessage() );
            @wp_redirect( remove_query_arg( self::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
            exit;
        }
    }

    /**
     * @param Inc\Mains\Booking\DataHolders\Order $order
     */
    private static function delete_bookings(Inc\Mains\Booking\DataHolders\Order $order )
    {
        foreach ($order->get_flat_services() as $item ) {
            $item->get_cb()->delete_cascade( );
        }
    }

    private static function get_api()
    {
        include_once Inc\Mains\Plugin::get_directory() . '/inc/payment/Mollie/API/Autoloader.php';
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey( get_option( 'bookme_mollie_api_key' ) );

        return $mollie;
    }

    /**
     * Notification for customer
     *
     * @param Inc\Mains\Booking\UserData $userData
     * @param string $status    success || error || processing
     * @param string $message
     */
    private static function redirect_to(Inc\Mains\Booking\UserData $userData, $status = 'success', $message = '' )
    {
        $userData->load();
        $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_MOLLIE, $status, $message );
        @wp_redirect( remove_query_arg( self::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
        exit;
    }

    /**
     * Get Mollie Payment
     *
     * @param string $tr_id
     * @return \Mollie_API_Object_Payment
     */
    public static function get_payment($tr_id )
    {
        $api = self::get_api();

        return $api->payments->get( $tr_id );
    }

}