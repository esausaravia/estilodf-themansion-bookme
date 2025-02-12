<?php
namespace Bookme\App\Front;

use Bookme\Inc;

/**
 * Class PayPal for Payment Gateway
 */
class PayPal extends Inc\Core\App
{
    /**
     * Init Express Checkout transaction.
     */
    public function ec_init()
    {
        $form_id = Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' );
        if ( $form_id ) {
            // Create a PayPal object.
            $paypal   = new Inc\Payment\PayPal();
            $userData = new Inc\Mains\Booking\UserData( $form_id );

            if ( $userData->load() ) {
                list ( $total, $deposit ) = $userData->cart->get_info();
                $product = new \stdClass();
                $product->name  = $userData->cart->get_items_title( 126 );
                $product->price = $deposit;
                $product->qty   = 1;
                $paypal->add_product( $product );

                // and send the payment request.
                $paypal->send_ec_request( $form_id );
            }
        }
    }

    /**
     * Process Express Checkout return request.
     */
    public function ec_return()
    {
        $form_id = Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' );
        $PayPal  = new Inc\Payment\PayPal();
        $error_message = '';

        if ( Inc\Mains\Functions\Request::has_parameter( 'token' ) && Inc\Mains\Functions\Request::has_parameter( 'PayerID' ) ) {
            $token = Inc\Mains\Functions\Request::get_parameter( 'token' );
            $data = array( 'TOKEN' => $token );
            // Send the request to PayPal.
            $response = $PayPal->send_nvp_request( 'GetExpressCheckoutDetails', $data );
            if ( $response == null ) {
                $error_message = $PayPal->get_error();
            } elseif ( strtoupper( $response['ACK'] ) == 'SUCCESS' ) {
                $data['PAYERID'] = Inc\Mains\Functions\Request::get_parameter( 'PayerID' );
                $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';

                foreach ( array( 'PAYMENTREQUEST_0_AMT', 'PAYMENTREQUEST_0_ITEMAMT', 'PAYMENTREQUEST_0_CURRENCYCODE', 'L_PAYMENTREQUEST_0' ) as $parameter ) {
                    if ( array_key_exists( $parameter, $response ) ) {
                        $data[ $parameter ] = $response[ $parameter ];
                    }
                }

                // We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
                $response = $PayPal->send_nvp_request( 'DoExpressCheckoutPayment', $data );
                if ( $response === null ) {
                    $error_message = $PayPal->get_error();
                } elseif ( 'SUCCESS' == strtoupper( $response['ACK'] ) || 'SUCCESSWITHWARNING' == strtoupper( $response['ACK'] ) ) {
                    // Get transaction info
                    $response = $PayPal->send_nvp_request( 'GetTransactionDetails', array( 'TRANSACTIONID' => $response['PAYMENTINFO_0_TRANSACTIONID'] ) );
                    if ( $response === null ) {
                        $error_message = $PayPal->get_error();
                    } elseif ( 'SUCCESS' == strtoupper( $response['ACK'] ) || 'SUCCESSWITHWARNING' == strtoupper( $response['ACK'] ) ) {
                        $userData = new Inc\Mains\Booking\UserData( $form_id );
                        $userData->load();
                        list ( $total, $deposit ) = $userData->cart->get_info();
                        $coupon = $userData->get_coupon();
                        if ( $coupon ) {
                            $coupon->claim();
                            $coupon->save();
                        }
                        $payment = new Inc\Mains\Tables\Payment();
                        $payment
                            ->set_type( Inc\Mains\Tables\Payment::TYPE_PAYPAL )
                            ->set_status( Inc\Mains\Tables\Payment::STATUS_COMPLETED )
                            ->set_total( $total )
                            ->set_paid( $deposit )
                            ->set_paid_type( $total == $deposit ? Inc\Mains\Tables\Payment::PAY_IN_FULL : Inc\Mains\Tables\Payment::PAY_DEPOSIT )
                            ->set_created( current_time( 'mysql' ) )
                            ->save();
                        $order = $userData->save( $payment );
                        Inc\Mains\Notification\Sender::send_from_cart( $order );
                        $payment->set_details( $order, $coupon )->save();
                        $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_PAYPAL, 'success' );

                        @wp_redirect( remove_query_arg( Inc\Payment\PayPal::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
                        exit;
                    } else {
                        $error_message = $response['L_LONGMESSAGE0'];
                    }
                } else {
                    $error_message = $response['L_LONGMESSAGE0'];
                }
            }
        } else {
            $error_message = esc_html__( 'Invalid token provided', 'bookme' );
        }

        if ( ! empty( $error_message ) ) {
            header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                    'bookme_action' => 'paypal-ec-error',
                    'bookme_fid' => $form_id,
                    'error_msg'  => urlencode( $error_message ),
                ), Inc\Mains\Functions\System::get_current_page_url()
                ) ) );
            exit;
        }
    }

    /**
     * Process Express Checkout cancel request.
     */
    public function ec_cancel()
    {
        $userData = new Inc\Mains\Booking\UserData( Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' ) );
        $userData->load();
        $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_PAYPAL, 'cancelled' );
        @wp_redirect( remove_query_arg(Inc\Payment\PayPal::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
        exit;
    }

    /**
     * Process Express Checkout error request.
     */
    public function ec_error()
    {
        $userData = new Inc\Mains\Booking\UserData( Inc\Mains\Functions\Request::get_parameter( 'bookme_fid' ) );
        $userData->load();
        $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_PAYPAL, 'error', Inc\Mains\Functions\Request::get_parameter( 'error_msg' ) );
        @wp_redirect( remove_query_arg(Inc\Payment\PayPal::$remove_parameters, Inc\Mains\Functions\System::get_current_page_url() ) );
        exit;
    }
}