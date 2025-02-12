<?php defined('ABSPATH') or die('No script kiddies please!');

use Bookme\Inc\Mains\Tables\Payment;
?>
<form class="theme-form bm-ajax-form" data-tab="payments">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Payments', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="bookme_currency"><?php esc_html_e('Currency', 'bookme') ?></label>
                        <select id="bookme_currency" class="form-control" name="bookme_currency">
                            <?php foreach (\Bookme\Inc\Mains\Functions\Price::get_currencies() as $code => $currency) { ?>
                                <option value="<?php echo $code ?>"
                                        data-symbol="<?php echo esc_attr($currency['symbol']) ?>" <?php selected(get_option('bookme_currency'), $code) ?> ><?php echo $code ?>
                                    (<?php echo esc_html($currency['symbol']) ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="bookme_price_format"><?php esc_html_e('Price format', 'bookme') ?></label>
                        <select id="bookme_price_format" class="form-control"
                                name="bookme_price_format">
                            <?php foreach (\Bookme\Inc\Mains\Functions\Price::get_formats() as $format) { ?>
                                <option value="<?php echo $format ?>" <?php selected(get_option('bookme_price_format'), $format) ?> ></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="bookme_coupons_enabled">
                            <?php esc_html_e('Coupons', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Enable discount coupons.', 'bookme') ?>"
                               data-tippy-placement="top"></i></label>
                        <div class="form-toggle-option">
                            <div>
                                <label for="bookme_coupons_enabled"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="bookme_coupons_enabled" value="0">
                                <label class="switch switch-sm">
                                    <input name="bookme_coupons_enabled" type="checkbox" id="bookme_coupons_enabled"
                                           value="1" <?php checked(get_option('bookme_coupons_enabled'), 1) ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bm-accordion bm-payment-sortable" id="accordion">
                <?php
                foreach ($payments as $order){
                    switch ($order){
                        case  Payment::TYPE_LOCAL:
                            ?>
                            <!-- Local -->
                            <div class="form-group">
                                <div class="form-toggle-option">
                                    <div>
                                        <i class="icon-feather-menu bookme-reorder-icon"
                                           data-payment="<?php echo Payment::TYPE_LOCAL; ?>"
                                           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                                        <label for="bookme_local_enabled"><?php esc_html_e('Service paid locally', 'bookme') ?></label>
                                    </div>
                                    <div>
                                        <input type="hidden" name="bookme_local_enabled" value="disabled">
                                        <label class="switch switch-sm">
                                            <input name="bookme_local_enabled" type="checkbox" id="bookme_local_enabled"
                                                   value="1" <?php checked(get_option('bookme_local_enabled'), 1) ?>>
                                            <span class="switch-state"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php
                            break;
                        case  Payment::TYPE_PAYPAL:
                            ?>
                            <!-- Paypal -->
                            <div class="card bookme-card">
                                <div class="card-header">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="icon-feather-menu bookme-reorder-icon"
                                           data-payment="<?php echo Payment::TYPE_PAYPAL; ?>"
                                           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                                        <button class="btn btn-link pl-0 ml-1" data-toggle="collapse" data-target="#paypal"
                                                aria-expanded="false" aria-controls="paypal"
                                                type="button">
                                            <img src="<?php echo BOOKME_URL . 'assets/admin/images/payment/paypal.png' ?>"
                                                 alt="paypal" height="20">
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="paypal" aria-labelledby="paypal" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <select id="bookme_paypal_enabled" class="form-control" name="bookme_paypal_enabled">
                                                <option value="disabled"
                                                    <?php selected(get_option('bookme_paypal_enabled'), 'disabled') ?> >
                                                    <?php esc_html_e('Disabled', 'bookme') ?>
                                                </option>
                                                <option value="<?php echo \Bookme\Inc\Payment\PayPal::TYPE_EXPRESS_CHECKOUT ?>"
                                                    <?php selected(get_option('bookme_paypal_enabled'), \Bookme\Inc\Payment\PayPal::TYPE_EXPRESS_CHECKOUT) ?> >
                                                    <?php esc_html_e('PayPal Express Checkout', 'bookme') ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="bookme-paypal">
                                            <div class="form-group">
                                                <label for="bookme_paypal_api_username">
                                                    <?php esc_html_e('API Username', 'bookme') ?>
                                                </label>
                                                <input id="bookme_paypal_api_username" class="form-control" type="text"
                                                       name="bookme_paypal_api_username"
                                                       value="<?php echo esc_attr(get_option('bookme_paypal_api_username')) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="bookme_paypal_api_password">
                                                    <?php esc_html_e('API Password', 'bookme') ?>
                                                </label>
                                                <input id="bookme_paypal_api_password" class="form-control" type="text"
                                                       name="bookme_paypal_api_password"
                                                       value="<?php echo esc_attr(get_option('bookme_paypal_api_password')) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="bookme_paypal_api_signature">
                                                    <?php esc_html_e('API Signature', 'bookme') ?>
                                                </label>
                                                <input id="bookme_paypal_api_signature" class="form-control" type="text"
                                                       name="bookme_paypal_api_signature"
                                                       value="<?php echo esc_attr(get_option('bookme_paypal_api_signature')) ?>">
                                            </div>
                                            <label for="bookme_paypal_sandbox">
                                                <?php esc_html_e('Sandbox Mode', 'bookme') ?>
                                            </label>
                                            <div class="form-toggle-option">
                                                <div>
                                                    <label for="bookme_paypal_sandbox"><?php esc_html_e('Enable', 'bookme') ?></label>
                                                </div>
                                                <div>
                                                    <input type="hidden" name="bookme_paypal_sandbox" value="0">
                                                    <label class="switch switch-sm">
                                                        <input name="bookme_paypal_sandbox" type="checkbox"
                                                               id="bookme_paypal_sandbox"
                                                               value="1" <?php checked(get_option('bookme_paypal_sandbox', 1)) ?>>
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            break;
                        case  Payment::TYPE_STRIPE:
                            ?>
                            <!-- Stripe -->
                            <div class="card bookme-card">
                                <div class="card-header">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="icon-feather-menu bookme-reorder-icon"
                                           data-payment="<?php echo Payment::TYPE_STRIPE; ?>"
                                           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                                        <button class="btn btn-link pl-0 ml-1" data-toggle="collapse" data-target="#stripe"
                                                aria-expanded="false" aria-controls="stripe"
                                                type="button">
                                            <img src="<?php echo BOOKME_URL . 'assets/admin/images/payment/stripe.png' ?>"
                                                 alt="stripe" height="20">
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="stripe" aria-labelledby="stripe" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <h5><?php esc_html_e( 'Instructions', 'bookme' ) ?></h5>
                                            <ol class="p-0">
                                                <li><?php printf( __( 'Enter <b>Secret</b> and <b>Publishable</b> keys, which are available in the <a href="%s" target="_blank">Stripe Dashboard</a>, in the below form.', 'bookme' ), 'https://dashboard.stripe.com/account/apikeys' ) ?></li>
                                                <li><?php printf( __( 'In the Stripe Dashboard\'s <a href="%s" target="_blank">Webhooks settings</a> section, click <strong>Add endpoint</strong> to add a new endpoint.', 'bookme' ), 'https://dashboard.stripe.com/account/webhooks' ) ?></li>
                                                <li><?php printf( __( 'Enter the following URL as the destination for events <mark>%s</mark>.', 'bookme' ), admin_url( 'admin-ajax.php?action=bookme_stripe_webhook' ) ) ?></li>
                                                <li><?php _e( 'Select these events: <strong>payment_intent.succeeded</strong>, <strong>payment_intent.payment_failed</strong> and click <strong>Add endpoint</strong>.', 'bookme' ) ?></li>
                                            </ol>
                                        </div>
                                        <div class="form-group">
                                            <select id="bookme_stripe_enabled" class="form-control" name="bookme_stripe_enabled">
                                                <option value="disabled"
                                                    <?php selected(get_option('bookme_stripe_enabled'), 'disabled') ?> >
                                                    <?php esc_html_e('Disabled', 'bookme') ?>
                                                </option>
                                                <option value="1"
                                                    <?php selected(get_option('bookme_stripe_enabled'), '1') ?> >
                                                    <?php esc_html_e('Enabled', 'bookme') ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="bookme-stripe">
                                            <div class="form-group">
                                                <label for="bookme_stripe_secret_key">
                                                    <?php esc_html_e('Secret Key', 'bookme') ?>
                                                </label>
                                                <input id="bookme_stripe_secret_key" class="form-control" type="text"
                                                       name="bookme_stripe_secret_key"
                                                       value="<?php echo esc_attr(get_option('bookme_stripe_secret_key')) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="bookme_stripe_publishable_key">
                                                    <?php esc_html_e('Publishable Key', 'bookme') ?>
                                                </label>
                                                <input id="bookme_stripe_publishable_key" class="form-control" type="text"
                                                       name="bookme_stripe_publishable_key"
                                                       value="<?php echo esc_attr(get_option('bookme_stripe_publishable_key')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            break;
                        case  Payment::TYPE_RAZORPAY:
                            ?>
                            <!-- Razorpay -->
                            <div class="card bookme-card">
                                <div class="card-header">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="icon-feather-menu bookme-reorder-icon"
                                           data-payment="<?php echo Payment::TYPE_RAZORPAY; ?>"
                                           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                                        <button class="btn btn-link pl-0 ml-1" data-toggle="collapse" data-target="#razorpay"
                                                aria-expanded="false" aria-controls="razorpay"
                                                type="button">
                                            <img src="<?php echo BOOKME_URL . 'assets/admin/images/payment/razorpay.png' ?>"
                                                 alt="razorpay" height="20">
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="razorpay" aria-labelledby="razorpay" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <select id="bookme_razorpay_enabled" class="form-control" name="bookme_razorpay_enabled">
                                                <option value="disabled"
                                                    <?php selected(get_option('bookme_razorpay_enabled'), 'disabled') ?> >
                                                    <?php esc_html_e('Disabled', 'bookme') ?>
                                                </option>
                                                <option value="1"
                                                    <?php selected(get_option('bookme_razorpay_enabled'), '1') ?> >
                                                    <?php esc_html_e('Enabled', 'bookme') ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="bookme-razorpay">
                                            <div class="form-group">
                                                <label for="bookme_razorpay_api_key">
                                                    <?php esc_html_e('API Key', 'bookme') ?>
                                                </label>
                                                <input id="bookme_razorpay_api_key" class="form-control" type="text"
                                                       name="bookme_razorpay_api_key"
                                                       value="<?php echo esc_attr(get_option('bookme_razorpay_api_key')) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="bookme_razorpay_secret_key">
                                                    <?php esc_html_e('Secret Key', 'bookme') ?>
                                                </label>
                                                <input id="bookme_razorpay_secret_key" class="form-control" type="text"
                                                       name="bookme_razorpay_secret_key"
                                                       value="<?php echo esc_attr(get_option('bookme_razorpay_secret_key')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            break;
                        case  Payment::TYPE_2CHECKOUT:
                            ?>
                            <!-- 2checkout -->
                            <div class="card bookme-card">
                                <div class="card-header">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="icon-feather-menu bookme-reorder-icon"
                                           data-payment="<?php echo Payment::TYPE_2CHECKOUT; ?>"
                                           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                                        <button class="btn btn-link pl-0 ml-1" data-toggle="collapse" data-target="#2checkout"
                                                aria-expanded="false" aria-controls="2checkout"
                                                type="button">
                                            <img src="<?php echo BOOKME_URL . 'assets/admin/images/payment/2checkout.png' ?>"
                                                 alt="2checkout" height="15">
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="2checkout" aria-labelledby="2checkout" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <select id="bookme_2checkout_enabled" class="form-control"
                                                    name="bookme_2checkout_enabled">
                                                <option value="disabled"
                                                    <?php selected(get_option('bookme_2checkout_enabled'), 'disabled') ?> >
                                                    <?php esc_html_e('Disabled', 'bookme') ?>
                                                </option>
                                                <option value="standard_checkout"
                                                    <?php selected(get_option('bookme_2checkout_enabled'), 'standard_checkout') ?> >
                                                    <?php esc_html_e('2Checkout Standard Checkout', 'bookme') ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="bookme-2checkout">
                                            <p>
                                                <?php _e('In <strong>Checkout Options</strong> of your 2Checkout account do the following steps:', 'bookme') ?>
                                            </p>
                                            <ol>
                                                <li><?php _e('In <strong>Direct Return</strong> select <b>Header Redirect (Your URL)</b>.', 'bookme') ?></li>
                                                <li><?php _e('In <strong>Approved URL</strong> enter the URL of your booking page.', 'bookme') ?></li>
                                            </ol>
                                            <div class="form-group">
                                                <label for="bookme_2checkout_api_seller_id">
                                                    <?php esc_html_e('Account Number', 'bookme') ?>
                                                </label>
                                                <input id="bookme_2checkout_api_seller_id" class="form-control" type="text"
                                                       name="bookme_2checkout_api_seller_id"
                                                       value="<?php echo esc_attr(get_option('bookme_2checkout_api_seller_id')) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="bookme_2checkout_api_secret_word">
                                                    <?php esc_html_e('Secret Word', 'bookme') ?>
                                                </label>
                                                <input id="bookme_2checkout_api_secret_word" class="form-control"
                                                       type="text"
                                                       name="bookme_2checkout_api_secret_word"
                                                       value="<?php echo esc_attr(get_option('bookme_2checkout_api_secret_word')) ?>">
                                            </div>
                                            <label for="bookme_2checkout_sandbox">
                                                <?php esc_html_e('Sandbox Mode', 'bookme') ?>
                                            </label>
                                            <div class="form-toggle-option">
                                                <div>
                                                    <label for="bookme_2checkout_sandbox"><?php esc_html_e('Enable', 'bookme') ?></label>
                                                </div>
                                                <div>
                                                    <input type="hidden" name="bookme_2checkout_sandbox" value="0">
                                                    <label class="switch switch-sm">
                                                        <input name="bookme_2checkout_sandbox" type="checkbox"
                                                               id="bookme_2checkout_sandbox"
                                                               value="1" <?php checked(get_option('bookme_2checkout_sandbox', 1)) ?>>
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            break;
                        case  Payment::TYPE_AUTHORIZENET:
                            ?>
                            <!-- authorize.net -->
                            <div class="card bookme-card">
                                <div class="card-header">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="icon-feather-menu bookme-reorder-icon"
                                           data-payment="<?php echo Payment::TYPE_AUTHORIZENET; ?>"
                                           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                                        <button class="btn btn-link pl-0 ml-1" data-toggle="collapse" data-target="#authorize"
                                                aria-expanded="false" aria-controls="authorize"
                                                type="button">
                                            <img src="<?php echo BOOKME_URL . 'assets/admin/images/payment/authorizenet.png' ?>"
                                                 alt="authorize.net" height="15">
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="authorize" aria-labelledby="authorize" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <select id="bookme_authorize_net_enabled" class="form-control"
                                                    name="bookme_authorize_net_enabled">
                                                <option value="disabled"
                                                    <?php selected(get_option('bookme_authorize_net_enabled'), 'disabled') ?> >
                                                    <?php esc_html_e('Disabled', 'bookme') ?>
                                                </option>
                                                <option value="aim"
                                                    <?php selected(get_option('bookme_authorize_net_enabled'), 'aim') ?> >
                                                    <?php esc_html_e('Authorize.Net AIM', 'bookme') ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="bookme-authorize-net">
                                            <div class="form-group">
                                                <label for="bookme_authorize_net_api_login_id">
                                                    <?php esc_html_e('API Login ID', 'bookme') ?>
                                                </label>
                                                <input id="bookme_authorize_net_api_login_id" class="form-control"
                                                       type="text"
                                                       name="bookme_authorize_net_api_login_id"
                                                       value="<?php echo esc_attr(get_option('bookme_authorize_net_api_login_id')) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="bookme_authorize_net_transaction_key">
                                                    <?php esc_html_e('API Transaction Key', 'bookme') ?>
                                                </label>
                                                <input id="bookme_authorize_net_transaction_key" class="form-control"
                                                       type="text"
                                                       name="bookme_authorize_net_transaction_key"
                                                       value="<?php echo esc_attr(get_option('bookme_authorize_net_transaction_key')) ?>">
                                            </div>
                                            <label for="bookme_authorize_net_sandbox">
                                                <?php esc_html_e('Sandbox Mode', 'bookme') ?>
                                            </label>
                                            <div class="form-toggle-option">
                                                <div>
                                                    <label for="bookme_authorize_net_sandbox"><?php esc_html_e('Enable', 'bookme') ?></label>
                                                </div>
                                                <div>
                                                    <input type="hidden" name="bookme_authorize_net_sandbox" value="0">
                                                    <label class="switch switch-sm">
                                                        <input name="bookme_authorize_net_sandbox" type="checkbox"
                                                               id="bookme_authorize_net_sandbox"
                                                               value="1" <?php checked(get_option('bookme_authorize_net_sandbox', 1)) ?>>
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            break;
                        case  Payment::TYPE_MOLLIE:
                            ?>
                            <!-- mollie -->
                            <div class="card bookme-card m-b-0">
                                <div class="card-header">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="icon-feather-menu bookme-reorder-icon"
                                           data-payment="<?php echo Payment::TYPE_MOLLIE; ?>"
                                           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                                        <button class="btn btn-link pl-0 ml-1" data-toggle="collapse" data-target="#mollie"
                                                aria-expanded="false" aria-controls="mollie"
                                                type="button">
                                            <img src="<?php echo BOOKME_URL . 'assets/admin/images/payment/mollie.png' ?>"
                                                 alt="mollie" height="15" style="vertical-align: initial">
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="mollie" aria-labelledby="mollie" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <select id="bookme_mollie_enabled" class="form-control" name="bookme_mollie_enabled">
                                                <option value="disabled"
                                                    <?php selected(get_option('bookme_mollie_enabled'), 'disabled') ?> >
                                                    <?php esc_html_e('Disabled', 'bookme') ?>
                                                </option>
                                                <option value="1"
                                                    <?php selected(get_option('bookme_mollie_enabled'), '1') ?> >
                                                    <?php esc_html_e('Enabled', 'bookme') ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="bookme-mollie">
                                            <div class="form-group">
                                                <label for="bookme_mollie_api_key">
                                                    <?php esc_html_e('API Key', 'bookme') ?>
                                                </label>
                                                <input id="bookme_mollie_api_key" class="form-control" type="text"
                                                       name="bookme_mollie_api_key"
                                                       value="<?php echo esc_attr(get_option('bookme_mollie_api_key')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            break;
                    }
                }
                ?>
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>