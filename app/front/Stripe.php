<?php

namespace Bookme\App\Front;

use Bookme\Inc;

/**
 * Class Stripe for Payment Gateway
 */
class Stripe extends Inc\Core\App
{
    /** @var array Zero-decimal currencies */
    private $zero_decimal = array('BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',);


    public function perform_stripe()
    {
        $response = null;
        $userData = new Inc\Mains\Booking\UserData(Inc\Mains\Functions\Request::get_parameter('form_id'));

        if ($userData->load()) {
            $failed_cart_key = $userData->cart->get_failed_cart_key();
            if ($failed_cart_key === null) {
                include_once Inc\Mains\Plugin::get_directory() . '/inc/payment/Stripe/init.php';
                \Stripe\Stripe::setApiKey(get_option('bookme_stripe_secret_key'));
                \Stripe\Stripe::setApiVersion('2015-08-19');

                list($total, $deposit) = $userData->cart->get_info();
                try {
                    if (in_array(get_option('bookme_currency'), $this->zero_decimal)) {
                        // Zero-decimal currency
                        $stripe_amount = $deposit;
                    } else {
                        $stripe_amount = $deposit * 100; // amount in cents
                    }
                    $charge = \Stripe\Charge::create(array(
                        'amount' => (int)$stripe_amount,
                        'currency' => get_option('bookme_currency'),
                        'source' => Inc\Mains\Functions\Request::get_parameter('card'), // contain token or card data
                        'description' => 'Charge for ' . $userData->get('email')
                    ));

                    if ($charge->paid) {
                        $coupon = $userData->get_coupon();
                        if ($coupon) {
                            $coupon->claim();
                            $coupon->save();
                        }
                        $payment = new Inc\Mains\Tables\Payment();
                        $payment
                            ->set_type(Inc\Mains\Tables\Payment::TYPE_STRIPE)
                            ->set_status(Inc\Mains\Tables\Payment::STATUS_COMPLETED)
                            ->set_total($total)
                            ->set_paid($deposit)
                            ->set_paid_type($total == $deposit ? Inc\Mains\Tables\Payment::PAY_IN_FULL : Inc\Mains\Tables\Payment::PAY_DEPOSIT)
                            ->set_created(current_time('mysql'))
                            ->save();
                        $order = $userData->save($payment);
                        Inc\Mains\Notification\Sender::send_from_cart($order);
                        $payment->set_details($order, $coupon)->save();

                        $response = array('success' => true);
                    } else {
                        $response = array('success' => false, 'error' => __('Unexpected Error, try again.', 'bookme'));
                    }
                } catch (\Exception $e) {
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

        wp_send_json($response);
    }

    public static function perform_stripe_create_intent()
    {
        $response = null;
        $form_id = Inc\Mains\Functions\Request::get_parameter('form_id');
        $userData = new Inc\Mains\Booking\UserData($form_id);

        if ($userData->load()) {
            $failed_cart_key = $userData->cart->get_failed_cart_key();
            if ($failed_cart_key === null) {
                include_once Inc\Mains\Plugin::get_directory() . '/inc/payment/Stripe/init.php';
                Inc\Payment\Stripe\Lib\Stripe::setApiKey(get_option('bookme_stripe_secret_key'));
                Inc\Payment\Stripe\Lib\Stripe::setApiVersion('2019-02-19');

                list($total, $deposit) = $userData->cart->get_info();
                $payment = null;
                try {
                    if (in_array(get_option('bookme_currency'), array('BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',))) {
                        // Zero-decimal currency
                        $stripe_amount = $deposit;
                    } else {
                        $stripe_amount = $deposit * 100; // amount in cents
                    }

                    $coupon = $userData->get_coupon();
                    if ($coupon) {
                        $coupon->claim();
                        $coupon->save();
                    }

                    $payment = new Inc\Mains\Tables\Payment();
                    $payment
                        ->set_type(Inc\Mains\Tables\Payment::TYPE_STRIPE)
                        ->set_status(Inc\Mains\Tables\Payment::STATUS_PENDING)
                        ->set_total($total)
                        ->set_paid($deposit)
                        ->set_paid_type($total == $deposit ? Inc\Mains\Tables\Payment::PAY_IN_FULL : Inc\Mains\Tables\Payment::PAY_DEPOSIT)
                        ->set_created(current_time('mysql'))
                        ->save();

                    $intent = Inc\Payment\Stripe\Lib\PaymentIntent::create(array(
                        'amount' => round($stripe_amount),
                        'currency' => get_option('bookme_currency'),
                        'payment_method_types' => array('card'),
                        'description' => $userData->cart->get_items_title() . ' for ' . $userData->get('email'),
                        'receipt_email' => $userData->get('email'),
                        'metadata' => array(
                            'payment_id' => $payment->get_id(),
                            'description' => $userData->cart->get_items_title(),
                            'customer' => $userData->get('full_name'),
                            'email' => $userData->get('email'),
                        ),
                    ));
                    if ($intent->client_secret) {
                        $userData->set_payment_status(Inc\Mains\Tables\Payment::TYPE_STRIPE, 'processing');
                        $order = $userData->save($payment);
                        $payment->set_details($order, $coupon)->save();
                        $response = array('success' => true, 'intent_secret' => $intent->client_secret, 'intent_id' => $intent->id);
                    } else {
                        $payment->delete();
                        $response = array('success' => false, 'error' => __('Error', 'bookme'));
                    }
                } catch (\Exception $e) {
                    if ($payment !== null) {
                        $payment->delete();
                    }
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

    public static function perform_stripe_failed_payment()
    {
        $response = null;
        $userData = new Inc\Mains\Booking\UserData(Inc\Mains\Functions\Request::get_parameter('form_id'));
        $intent_id = Inc\Mains\Functions\Request::get_parameter('intent_id');

        if ($userData->load()) {
            include_once Inc\Mains\Plugin::get_directory() . '/inc/payment/Stripe/init.php';
            Inc\Payment\Stripe\Lib\Stripe::setApiKey(get_option('bookme_stripe_secret_key'));
            Inc\Payment\Stripe\Lib\Stripe::setApiVersion('2019-02-19');
            $intent = Inc\Payment\Stripe\Lib\PaymentIntent::retrieve($intent_id);

            $payment = $intent ? Inc\Mains\Tables\Payment::find($intent->metadata->payment_id) : null;
            if ($payment) {
                if($payment->get_type() == Inc\Mains\Tables\Payment::TYPE_STRIPE){
                    self::delete_bookings($intent->metadata->payment_id);
                    $payment->delete();
                }
            }
            $userData->set_payment_status( Inc\Mains\Tables\Payment::TYPE_STRIPE, 'success' );
            $response = array('success' => true);
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }
        $userData->save_in_session();

        // Output JSON response.
        wp_send_json($response);
    }

    public static function perform_stripe_process_payment()
    {
        wp_send_json_success();
    }

    public static function perform_stripe_webhook()
    {
        $input      = @file_get_contents( 'php://input' );
        $event_json = json_decode( $input, true );
        if ( $event_json && isset( $event_json['type'] ) && in_array( $event_json['type'], array( 'payment_intent.succeeded', 'payment_intent.payment_failed' ) ) ) {
            $intent_id = $event_json['data']['object']['id'];
            include_once Inc\Mains\Plugin::get_directory() . '/inc/payment/Stripe/init.php';
            Inc\Payment\Stripe\Lib\Stripe::setApiKey(get_option('bookme_stripe_secret_key'));
            Inc\Payment\Stripe\Lib\Stripe::setApiVersion('2019-02-19');
            $intent = Inc\Payment\Stripe\Lib\PaymentIntent::retrieve( $intent_id );
            if ( $intent ) {
                $metadata = $intent->metadata;
                $payment  = Inc\Mains\Tables\Payment::find($intent->metadata->payment_id);
                switch ( $intent->status ) {
                    case 'succeeded':
                        $total    = (float) $payment->get_paid();
                        $received = (float) $intent->amount_received;
                        if ( ! in_array( get_option( 'bookme_currency' ), array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF', ) ) ) {
                            $total = $total * 100; // amount in cents
                        }
                        if ( abs( $received - $total ) <= 0.01 && strtolower( get_option( 'bookme_currency' ) ) == $intent->currency ) {
                            $payment->set_status( Inc\Mains\Tables\Payment::STATUS_COMPLETED )->save();
                            if ( $order = Inc\Mains\Booking\DataHolders\Order::create_from_payment( $payment ) ) {
                                Inc\Mains\Notification\Sender::send_from_cart( $order );
                            }
                            // TODO: sync google calendar
                        }
                        break;
                    default:
                        if ( $event_json['type'] == 'payment_intent.payment_failed' ) {
                            self::delete_bookings( $metadata->payment_id );
                            if ($payment) {
                                $payment->delete();
                            }
                        }
                        break;
                }
            }
        }

        wp_send_json_success();
    }

    private static function delete_bookings( $payment_id )
    {
        global $wpdb;
        $ca_list = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` 
                    WHERE payment_id = %d",
                $payment_id
            ),
            ARRAY_A);
        $ca_list = Inc\Mains\Functions\System::bind_data_with_table(Inc\Mains\Tables\CustomerBooking::class, $ca_list);

        /** @var Inc\Mains\Tables\CustomerBooking $ca */
        foreach ( $ca_list as $ca ) {
            $ca->delete_cascade();
        }
    }

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {
        Inc\Core\Ajax::register_ajax_actions($this, array('app' => 'everyone'), array('perform_stripe_webhook'), true);
    }
}