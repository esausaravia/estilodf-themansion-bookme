<?php

namespace Bookme\App\Front;

use Bookme\Inc;

/**
 * Class Razorpay for Payment Gateway
 */
class Razorpay extends Inc\Core\App
{

    public function perform_razorpay_init()
    {
        $response = null;
        $form_id = Inc\Mains\Functions\Request::get_parameter('form_id');
        $userData = new Inc\Mains\Booking\UserData($form_id);

        if ($userData->load()) {
            $failed_cart_key = $userData->cart->get_failed_cart_key();
            if ($failed_cart_key === null) {
                include_once Inc\Mains\Plugin::get_directory() . '/inc/payment/razorpay/Razorpay.php';

                $api_key = get_option('bookme_razorpay_api_key');
                $secret_key = get_option('bookme_razorpay_secret_key');
                $currency = get_option( 'bookme_currency' );

                $api = new \Razorpay\Api\Api($api_key, $secret_key);

                list($total, $deposit) = $userData->cart->get_info();
                //
                // We create an razorpay order using orders api
                // Docs: https://docs.razorpay.com/docs/orders
                //
                $order_data = [
                    'amount'          => $deposit * 100, // rupees in paise
                    'currency'        => $currency,
                    'payment_capture' => 1 // auto capture
                ];

                try{
                    $razorpayOrder = $api->order->create($order_data);

                    $razorpayOrderId = $razorpayOrder['id'];
                    Inc\Mains\Functions\Session::setFormVar($form_id, 'razorpay_order_id', $razorpayOrderId);
                    $amount = $order_data['amount'];

                    $data = [
                        "key"               => $api_key,
                        "amount"            => $amount,
                        "name"              => $userData->cart->get_items_title(),
                        "description"       => '',
                        "image"             => "",
                        "prefill"           => [
                            "name"              => $userData->get('full_name'),
                            "email"             => $userData->get('email')
                        ],
                        "notes"             => [
                            "Title"           => $userData->cart->get_items_title()
                        ],
                        "theme"             => [
                            "color"             => get_option('bookme_primary_color', '#6B76FF')
                        ],
                        "order_id"          => $razorpayOrderId,
                    ];

                    $response = array('success' => true, 'data' => $data);
                }catch(\Exception $e){
                    $response = array('success' => false, 'error' => $e->getMessage());
                }
            } else {
                $response = array(
                    'success' => false,
                    'failed_cart_key' => $failed_cart_key,
                    'error' => esc_html__('Selected time slot is not available anymore. Please, choose another time slot.', 'bookme'),
                );
            }
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }
        $userData->save_in_session();

        // Output JSON response.
        wp_send_json($response);
    }

    public function perform_razorpay_process_payment()
    {
        $success = true;
        $response = null;
        $form_id = Inc\Mains\Functions\Request::get_parameter('form_id');

        if (empty(Inc\Mains\Functions\Request::get_parameter('razorpay_payment_id')) === false)
        {
            include_once Inc\Mains\Plugin::get_directory() . '/inc/payment/razorpay/Razorpay.php';

            $api_key = get_option('bookme_razorpay_api_key');
            $secret_key = get_option('bookme_razorpay_secret_key');

            $api = new \Razorpay\Api\Api($api_key, $secret_key);
            try
            {
                $attributes = array(
                    'razorpay_order_id' => Inc\Mains\Functions\Session::getFormVar($form_id, 'razorpay_order_id'),
                    'razorpay_payment_id' => Inc\Mains\Functions\Request::get_parameter('razorpay_payment_id'),
                    'razorpay_signature' => Inc\Mains\Functions\Request::get_parameter('razorpay_signature')
                );

                $api->utility->verifyPaymentSignature($attributes);
            }
            catch(\Razorpay\Api\Errors\SignatureVerificationError $e)
            {
                $success = false;
                $response = array('success' => false, 'error' => $e->getMessage());
            }
        }

        if ($success === true)
        {
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
                ->set_type( Inc\Mains\Tables\Payment::TYPE_RAZORPAY )
                ->set_status( Inc\Mains\Tables\Payment::STATUS_COMPLETED )
                ->set_total( $total )
                ->set_paid( $deposit )
                ->set_paid_type( $total == $deposit ? Inc\Mains\Tables\Payment::PAY_IN_FULL : Inc\Mains\Tables\Payment::PAY_DEPOSIT )
                ->set_created( current_time( 'mysql' ) )
                ->save();
            $order = $userData->save( $payment );
            Inc\Mains\Notification\Sender::send_from_cart( $order );
            $payment->set_details( $order, $coupon )->save();

            $response = array('success' => true);
        }

        // Output JSON response.
        wp_send_json($response);
    }

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {
        Inc\Core\Ajax::register_ajax_actions($this, array('app' => 'everyone'), array(), true);
    }
}