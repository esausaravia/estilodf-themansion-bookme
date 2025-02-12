<?php

namespace Bookme\App\Front;

use Bookme\Inc;

/**
 * Class AuthorizeNet for Payment Gateway
 */
class AuthorizeNet extends Inc\Core\App
{
    /**
     * Perform payment
     */
    public function perform_authorize_net()
    {
        $response = null;
        $userData = new Inc\Mains\Booking\UserData(Inc\Mains\Functions\Request::get_parameter('form_id'));

        if ($userData->load()) {
            $failed_cart_key = $userData->cart->get_failed_cart_key();
            if ($failed_cart_key === null) {
                list($total, $deposit) = $userData->cart->get_info();
                $card = Inc\Mains\Functions\Request::get_parameter('card');
                $first_name = $userData->get('first_name');
                $last_name = $userData->get('last_name');
                // Check if defined First name
                if (!$first_name) {
                    $full_name = $userData->get('full_name');
                    $first_name = strtok($full_name, ' ');
                    $last_name = strtok('');
                }

                // Authorize.Net AIM Payment.
                $authorize = new Inc\Payment\AuthorizeNet(get_option('bookme_authorize_net_api_login_id'), get_option('bookme_authorize_net_transaction_key'), (bool)get_option('bookme_authorize_net_sandbox'));
                $authorize->setField('amount', $deposit);
                $authorize->setField('card_num', $card['number']);
                $authorize->setField('card_code', $card['cvc']);
                $authorize->setField('exp_date', $card['exp_month'] . '/' . $card['exp_year']);
                $authorize->setField('email', $userData->get('email'));
                $authorize->setField('phone', $userData->get('phone'));
                $authorize->setField('first_name', $first_name);
                if ($last_name) {
                    $authorize->setField('last_name', $last_name);
                }

                $aim_response = $authorize->authorize_and_capture();
                if ($aim_response->approved) {
                    $coupon = $userData->get_coupon();
                    if ($coupon) {
                        $coupon->claim();
                        $coupon->save();
                    }
                    $payment = new Inc\Mains\Tables\Payment();
                    $payment->set_type(Inc\Mains\Tables\Payment::TYPE_AUTHORIZENET)
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
                    $response = array('success' => false, 'error' => $aim_response->response_reason_text);
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

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {
        Inc\Core\Ajax::register_ajax_actions($this, array('app' => 'everyone'), array(), true);
    }
}