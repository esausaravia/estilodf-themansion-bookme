<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\Inc\Mains\Functions\System;
use Bookme\Inc\Mains\Tables\Payment;

echo $progress_bar; ?>
<div class="bookme-booking-detail-step">
    <p><?php esc_html_e('Please provide your details in the form below.', 'bookme') ?></p>

    <?php if (get_option('bookme_customer_show_login_button') && get_current_user_id() == 0) { ?>
        <div class="bookme-form-group">
            <button type="button"
                    class="bookme-button bookme-login-dialog-show"><?php esc_html_e('Login', 'bookme') ?></button>
        </div>
    <?php } ?>

    <div class="bookme-row<?php echo (int)get_option('bookme_form_layout') == 1 ? ' bookme-layout-1': ''; ?>">
        <div class="<?php echo !$disabled ? 'bookme-col-md-6' : 'bookme-col-12' ?>">
            <?php if (System::show_first_last_name()) { ?>
                <div class="bookme-form-group">
                    <label><?php esc_html_e('First Name', 'bookme') ?></label>
                    <input class="bookme-first-name" type="text"
                           value="<?php echo esc_attr($user_data->get('first_name')) ?>"/>
                    <div class="bookme-first-name-error bookme-form-error"></div>
                </div>
                <div class="bookme-form-group">
                    <label><?php esc_html_e('Last Name', 'bookme') ?></label>
                    <input class="bookme-last-name" type="text"
                           value="<?php echo esc_attr($user_data->get('last_name')) ?>"/>
                    <div class="bookme-last-name-error bookme-form-error"></div>
                </div>
            <?php } else { ?>
                <div class="bookme-form-group">
                    <label><?php esc_html_e('Name', 'bookme') ?></label>
                    <input class="bookme-full-name" type="text"
                           value="<?php echo esc_attr($user_data->get('full_name')) ?>"/>
                    <div class="bookme-full-name-error bookme-form-error"></div>
                </div>
            <?php } ?>
            <div class="bookme-form-group">
                <label><?php esc_html_e('Phone', 'bookme') ?></label>
                <div>
                    <input class="bookme-phone<?php if (get_option('bookme_phone_default_country') != 'disabled') { ?> bookme-phone-input<?php } ?>"
                           value="<?php echo esc_attr($user_data->get('phone')) ?>" type="text"/>
                </div>
                <div class="bookme-phone-error bookme-form-error"></div>
            </div>
            <div class="bookme-form-group">
                <label><?php esc_html_e('Email', 'bookme') ?></label>
                <div>
                    <input class="bookme-email" type="text" value="<?php echo esc_attr($user_data->get('email')) ?>"/>
                </div>
                <div class="bookme-email-error bookme-form-error"></div>
            </div>
            <?php foreach ($cf_data as $key => $cf_item) { ?>
                <div class="bookme-custom-fields-wrapper" data-key="<?php echo $key ?>">
                    <?php if ($show_service_title && !empty ($cf_item['custom_fields'])) { ?>
                        <div class="bookme-form-group"><strong><?php echo $cf_item['service_title'] ?></strong></div>
                    <?php } ?>
                    <?php foreach ($cf_item['custom_fields'] as $custom_field) { ?>
                        <div class="bookme-form-group" data-id="<?php echo $custom_field->id ?>"
                             data-type="<?php echo $custom_field->type ?>">
                            <?php if ($custom_field->type != 'text-content') { ?>
                                <label><?php echo $custom_field->label; ?></label>
                            <?php } ?>
                            <?php if ($custom_field->type == 'text-field') { ?>
                                <input type="text" class="bookme-custom-field"
                                       value="<?php echo isset($cf_item['data'][$custom_field->id]) ? esc_attr($cf_item['data'][$custom_field->id]) : '' ?>"/>
                            <?php } elseif ($custom_field->type == 'textarea') { ?>
                                <textarea rows="3"
                                          class="bookme-custom-field"><?php echo isset($cf_item['data'][$custom_field->id]) ? esc_textarea($cf_item['data'][$custom_field->id]) : '' ?></textarea>
                            <?php } elseif ($custom_field->type == 'text-content') { ?>
                                <?php echo nl2br($custom_field->label) ?>
                            <?php } elseif ($custom_field->type == 'checkboxes') { ?>
                                <div>
                                    <?php foreach ($custom_field->items as $item) { ?>
                                        <label>
                                            <input type="checkbox" class="bookme-custom-field"
                                                   value="<?php echo esc_attr($item['value']) ?>" <?php checked(in_array($item['value'], isset($cf_item['data'][$custom_field->id]) ? $cf_item['data'][$custom_field->id] : array()), true, true) ?> />
                                            <?php echo $item['label'] ?>
                                        </label><br/>
                                    <?php } ?>
                                </div>
                            <?php } elseif ($custom_field->type == 'radio-buttons') { ?>
                                <div>
                                    <?php foreach ($custom_field->items as $item) { ?>
                                        <label>
                                            <input type="radio" class="bookme-custom-field"
                                                   name="bookme-custom-field-<?php echo $custom_field->id ?>"
                                                   value="<?php echo esc_attr($item['value']) ?>" <?php checked($item['value'], isset($cf_item['data'][$custom_field->id]) ? $cf_item['data'][$custom_field->id] : '', true) ?> />
                                            <?php echo $item['label'] ?>
                                        </label><br/>
                                    <?php } ?>
                                </div>
                            <?php } elseif ($custom_field->type == 'drop-down') { ?>
                                <select class="bookme-custom-field">
                                    <option value=""></option>
                                    <?php foreach ($custom_field->items as $item) { ?>
                                        <option value="<?php echo esc_attr($item['value']) ?>" <?php selected($item['value'], isset($cf_item['data'][$custom_field->id]) ? $cf_item['data'][$custom_field->id] : '', true) ?>><?php echo esc_html($item['label']) ?></option>
                                    <?php } ?>
                                </select>
                            <?php }
                            if ($custom_field->type != 'text-content') { ?>
                                <div class="bookme-form-error bookme-custom-field-error"></div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php }
            if (get_option('bookme_captcha_enabled')) { ?>
                <div class="bookme-g-recaptcha"></div>
                <div class="bookme-form-error bookme-recaptcha-error"></div>
            <?php } ?>
        </div>
        <?php if (!$disabled) { ?>
            <div class="bookme-col-md-6">
                <?php foreach ($booking_data as $data) { ?>
                    <div class="bookme-detail-appointments">
                        <div class="bookme-d-flex">
                            <div>
                                <?php esc_html_e('Time', 'bookme') ?>
                            </div>
                            <div>
                                <?php echo $data['service_date'] . ' ' . $data['service_time'] ?>
                            </div>
                        </div>
                        <div class="bookme-d-flex">
                            <div>
                                <?php
                                $text = '<strong>' . $data['service_name'] . '</strong> ' .
                                    esc_html__('by', 'bookme') .
                                    ' <strong>' . $data['staff_name'] . '</strong>';

                                if ($data['number_of_persons'] > 1) {
                                    $text .= ' <strong>&times; ' . ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> ' . $data['number_of_persons'] . '</strong>';
                                }
                                echo $text;
                                ?>
                            </div>
                            <div>
                                <strong><?php echo $data['service_price']; ?></strong>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="bookme-detail-appointments">
                    <?php if (get_option('bookme_coupons_enabled')) { ?>
                        <div class="bookme-d-flex">
                            <div>
                                <?php esc_html_e('Sub Total', 'bookme') ?>
                            </div>
                            <div>
                                <strong><?php echo $sub_total ?></strong>
                            </div>
                        </div>
                        <div class="bookme-d-flex">
                            <div>
                                <?php esc_html_e('Discount', 'bookme') ?>
                            </div>
                            <div>
                                <strong class="bookme-discount-price"><?php echo $discount_price ?></strong>
                            </div>
                        </div>
                        <div class="bookme-d-flex">
                            <div>
                                <?php esc_html_e('Coupon', 'bookme') ?>
                                <div class="bookme-form-error bookme-coupon-error"></div>
                            </div>
                            <div>
                                <?php if ($coupon_code) { ?>
                                    <?php echo esc_attr($coupon_code) . ' <strong>✓</strong>' ?>
                                <?php } else { ?>
                                    <div class="bookme-d-flex">
                                        <input class="bookme-coupon-field" name="bookme-coupon-field" type="text"
                                               value="<?php echo esc_attr($coupon_code) ?>"/>
                                        <button class="bookme-button bookme-icon-button bookme-icon-button-sm bookme-apply-coupon"
                                                style="margin-left: 5px;">
                                            <strong>✓</strong>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="bookme-d-flex">
                        <div>
                            <strong><?php esc_html_e('Total', 'bookme') ?></strong>
                        </div>
                        <div>
                            <strong class="bookme-total-price"><?php echo $total ?></strong>
                        </div>
                    </div>
                </div>
                <?php if (!System::woo_commerce_enabled()){ ?>
                <div class="bookme-payment-wrapper">
                    <div class="bookme-form-group">
                        <strong><?php esc_html_e('Choose your payment method', 'bookme') ?></strong>
                    </div>
                    <div class="bookme-payment-methods">
                        <?php
                        foreach ($payment_methods as $payments) {
                            switch ($payments) {
                                case  Payment::TYPE_LOCAL:
                                    ?>
                                    <div class="bookme-form-group">
                                        <label>
                                            <input type="radio" class="bookme-payment" name="bookme-payment-<?php echo $form_id ?>" value="local" data-tab="local"/>
                                            <span><?php esc_html_e('Locally', 'bookme') ?></span>
                                        </label>
                                        <p class="bookme-pay-tab bookme-local" style="display: none;"><?php esc_html_e('I will pay locally at the meeting place.', 'bookme') ?></p>
                                    </div>
                                    <?php
                                    break;
                                case  Payment::TYPE_PAYPAL:
                                    ?>
                                    <div class="bookme-form-group">
                                        <label>
                                            <input type="radio" class="bookme-payment" name="bookme-payment-<?php echo $form_id ?>" value="paypal" data-tab="paypal"/>
                                            <span><?php esc_html_e('PayPal', 'bookme') ?></span>
                                        </label>
                                        <?php if ($payment['gateway'] == Payment::TYPE_PAYPAL && $payment['status'] == 'error') { ?>
                                            <div class="bookme-form-error"><?php echo $payment['data'] ?></div>
                                        <?php } ?>
                                        <div class="bookme-pay-tab bookme-paypal" style="display: none;">
                                            <p><?php esc_html_e('I will pay now with PayPal.', 'bookme') ?></p>
                                            <div class="pay-paypal">
                                                <?php if ($pay_paypal === \Bookme\Inc\Payment\PayPal::TYPE_EXPRESS_CHECKOUT) {
                                                    \Bookme\Inc\Payment\PayPal::render_ec_form($form_id);
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    break;
                                case  Payment::TYPE_AUTHORIZENET:
                                    ?>
                                    <div class="bookme-form-group">
                                        <label>
                                            <input type="radio" class="bookme-payment" name="bookme-payment-<?php echo $form_id ?>" value="card" data-tab="authorize-net"/>
                                            <span><?php esc_html_e('Credit/Debit Card', 'bookme') ?></span>
                                            <img src="<?php echo $cards_image_url ?>" alt="cards"/>
                                        </label>
                                        <form class="bookme-pay-tab bookme-authorize-net" style="display: none;">
                                            <?php include 'card_form.php' ?>
                                        </form>
                                    </div>
                                    <?php
                                    break;
                                case  Payment::TYPE_STRIPE:
                                    ?>
                                    <div class="bookme-form-group">
                                        <label>
                                            <input type="radio" class="bookme-payment" name="bookme-payment-<?php echo $form_id ?>" value="card" data-tab="stripe"/>
                                            <span><?php esc_html_e('Credit/Debit Card', 'bookme') ?></span>
                                            <img src="<?php echo $cards_image_url ?>" alt="cards"/>
                                        </label>
                                        <form class="bookme-pay-tab bookme-stripe" style="display: none;">
                                            <div id="bookme-stripe-card-element" style="padding: 10px;max-width: 400px;margin-top: 5px;border: 1px solid #ddd;border-radius: 2px !important;"></div>
                                            <div class="bookme-form-error bookme-card-error"></div>
                                        </form>
                                    </div>
                                    <?php
                                    break;
                                case  Payment::TYPE_RAZORPAY:
                                    ?>
                                    <div class="bookme-form-group">
                                        <label>
                                            <input type="radio" class="bookme-payment" name="bookme-payment-<?php echo $form_id ?>" value="card" data-tab="razorpay"/>
                                            <span><?php echo esc_html_x('Razorpay','Razorpay', 'bookme') ?></span>
                                        </label>
                                        <form class="bookme-pay-tab bookme-razorpay" style="display: none;">
                                            <div class="bookme-form-error bookme-card-error"></div>
                                        </form>
                                    </div>
                                    <?php
                                    break;
                                case  Payment::TYPE_2CHECKOUT:
                                    ?>
                                    <div class="bookme-form-group">
                                        <label>
                                            <input type="radio" class="bookme-payment" name="bookme-payment-<?php echo $form_id ?>" value="2checkout" data-tab="2checkout"/>
                                            <span><?php esc_html_e('Credit/Debit Card', 'bookme') ?></span>
                                            <img src="<?php echo $cards_image_url ?>" alt="cards"/>
                                        </label>
                                        <div class="bookme-pay-tab bookme-2checkout" style="display: none;">
                                            <?php \Bookme\Inc\Payment\TwoCheckout::render_form($form_id, $page_url) ?>
                                        </div>
                                    </div>
                                    <?php
                                    break;
                                case  Payment::TYPE_MOLLIE:
                                    ?>
                                    <div class="bookme-form-group">
                                        <label>
                                            <input type="radio" class="bookme-payment" name="bookme-payment-<?php echo $form_id ?>" value="mollie" data-tab="mollie"/>
                                            <span><?php esc_html_e('Mollie', 'bookme') ?></span>
                                        </label>
                                        <?php if ($payment['gateway'] == Payment::TYPE_MOLLIE && $payment['status'] == 'error'){ ?>
                                            <div class="bookme-form-error"><?php echo $payment['data'] ?></div>
                                        <?php } ?>
                                        <div class="bookme-pay-tab bookme-mollie" style="display: none;">
                                            <?php \Bookme\Inc\Payment\Mollie::render_form($form_id, $page_url) ?>
                                        </div>
                                    </div>
                                    <?php
                                    break;
                            }
                        }
                        ?>

                        <div class="bookme-form-group" style="display: none">
                            <input type="radio" class="bookme-payment bookme-coupon-free" name="bookme-payment-<?php echo $form_id ?>" value="coupon"/>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <div class="bookme-step-buttons">
        <div class="bookme-step-buttons-left">
            <button type="button" class="bookme-button bookme-back"><?php esc_html_e('Back', 'bookme') ?></button>
        </div>
        <button type="button" class="bookme-button bookme-next"><?php esc_html_e('Next', 'bookme') ?></button>
    </div>
</div>
