<?php
namespace Bookme\App\Front;

use Bookme\Inc;

/**
 * Class TwoCheckout for Payment Gateway
 */
class TwoCheckout extends Inc\Core\App
{
    public function approved()
    {
        $userData = new Inc\Mains\Booking\UserData( Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' ) );
        if ( ( $redirect_url = Inc\Mains\Functions\Request::get_parameter( 'x_receipt_link_url', false ) ) === false ) {
            // Clean GET parameters from 2Checkout.
            $redirect_url = remove_query_arg( Inc\Payment\TwoCheckout::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() );
        }
        if ( $userData->load() ) {
            list( $total, $deposit ) = $userData->cart->get_info();
            $amount = number_format( $deposit, 2, '.', '' );
            $compare_key = strtoupper( md5( get_option( 'bookme_2checkout_api_secret_word' ) . get_option( 'bookme_2checkout_api_seller_id' ) . Inc\Mains\Functions\Request::get_parameter( 'order_number' ) . $amount ) );
            if ( $compare_key != Inc\Mains\Functions\Request::get_parameter( 'key' ) ) {
                header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                        'bookme_action' => '2checkout-error',
                        'bookme_fid' =>Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' ),
                        'error_msg'  => urlencode( esc_html__( 'Invalid token provided', 'bookme' ) ),
                    ), Inc\Mains\Functions\System::get_current_page_url()
                    ) ) );
                exit;
            } else {
                $coupon = $userData->get_coupon();
                if ( $coupon ) {
                    $coupon->claim();
                    $coupon->save();
                }
                $payment = new Inc\Mains\Tables\Payment();
                $payment->set_type( Inc\Mains\Tables\Payment::TYPE_2CHECKOUT )
                    ->set_status( Inc\Mains\Tables\Payment::STATUS_COMPLETED )
                    ->set_total( $total )
                    ->set_paid( $deposit )
                    ->set_paid_type( $total == $deposit ? Inc\Mains\Tables\Payment::PAY_IN_FULL : Inc\Mains\Tables\Payment::PAY_DEPOSIT )
                    ->set_created( current_time( 'mysql' ) )
                    ->save();
                $order = $userData->save( $payment );
               Inc\Mains\Notification\Sender::send_from_cart( $order );
                $payment->set_details( $order, $coupon )->save();

                $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_2CHECKOUT, 'success' );

                @wp_redirect( $redirect_url );
                exit;
            }
        } else {
            header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                    'bookme_action' => '2checkout-error',
                    'bookme_fid' => Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' ),
                    'error_msg'  => urlencode( esc_html__( 'Invalid session data.', 'bookme' ) ),
                ), $redirect_url
                ) ) );
            exit;
        }
    }

    public function error()
    {
        $userData = new Inc\Mains\Booking\UserData( Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' ) );
        $userData->load();
        $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_2CHECKOUT, 'error', Inc\Mains\Functions\Request::get_parameter( 'error_msg' ) );
        @wp_redirect( remove_query_arg( Inc\Payment\TwoCheckout::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
        exit;
    }
}