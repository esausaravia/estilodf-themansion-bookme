<?php
namespace Bookme\Inc\Payment;

use Bookme\Inc;

/**
 * Class TwoCheckout
 */
class TwoCheckout
{
    // Array for cleaning 2Checkout request
    public static $remove_parameters = array( 'bookme_action', 'bookme_fid', 'error_msg', 'sid', 'middle_initial', 'li_0_name', 'key', 'email', 'li_0_type', 'lang', 'currency_code', 'invoice_id', 'li_0_price', 'total', 'credit_card_processed', 'zip', 'li_0_quantity', 'cart_weight', 'fixed', 'last_name', 'li_0_product_id', 'street_address', 'city', 'li_0_tangible', 'li_0_description', 'ip_country', 'country', 'merchant_order_id', 'pay_method', 'cart_tangible', 'phone', 'street_address2', 'x_receipt_link_url', 'first_name', 'card_holder_name', 'state', 'order_number', 'type', );

    public static function render_form($form_id, $page_url )
    {
        $userData = new Inc\Mains\Booking\UserData( $form_id );
        if ( $userData->load() ) {
            list( $total, $deposit ) = $userData->cart->get_info();
            $replacement = array(
                '%action%'    => get_option( 'bookme_2checkout_sandbox' ) == 1
                    ? 'https://sandbox.2checkout.com/checkout/purchase'
                    : 'https://www.2checkout.com/checkout/purchase',
                '%x_receipt_link_url%' => esc_attr( $page_url ),
                '%card_holder_name%' => esc_attr( $userData->get( 'full_name' ) ),
                '%currency_code%'    => get_option( 'bookme_currency' ),
                '%email%'     => esc_attr( $userData->get( 'email' ) ),
                '%form_id%'   => $form_id,
                '%gateway%'   => Inc\Mains\Tables\Payment::TYPE_2CHECKOUT,
                '%name%'      => esc_attr( $userData->cart->get_items_title( 128, false ) ),
                '%price%'     => $deposit,
                '%seller_id%' => get_option( 'bookme_2checkout_api_seller_id' ),
            );

            $form = '<form action="%action%" method="post" class="bookme-%gateway%-form">
                <input type="hidden" name="bookme_fid" value="%form_id%">
                <input type="hidden" name="card_holder_name" value="%card_holder_name%">
                <input type="hidden" name="currency_code" value="%currency_code%">
                <input type="hidden" name="email" value="%email%">
                <input type="hidden" name="bookme_action" value="2checkout-approved">
                <input type="hidden" name="li_0_name" value="%name%">
                <input type="hidden" name="li_0_price" value="%price%" class="bookme-payment-amount">
                <input type="hidden" name="li_0_quantity" value="1">
                <input type="hidden" name="li_0_tangible" value="N">
                <input type="hidden" name="li_0_type" value="product">
                <input type="hidden" name="mode" value="2CO">
                <input type="hidden" name="sid" value="%seller_id%">
                <input type="hidden" name="x_receipt_link_url" value="%x_receipt_link_url%">
            </form>';

            echo strtr( $form, $replacement );
        }
    }

}