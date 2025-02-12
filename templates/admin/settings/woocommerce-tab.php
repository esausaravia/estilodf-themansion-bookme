<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="woo_commerce">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('WooCommerce', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <?php if ($wc_error) { ?>
                <div class="alert alert-danger">
                    <?php echo $wc_error ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <h5><?php esc_html_e('Instructions', 'bookme') ?></h5>
                <p>
                    <?php _e('First install and activate the WooCommerce plugin.<br/>After activating the plugin follow the below steps:', 'bookme') ?>
                </p>
                <ol>
                    <li><?php esc_html_e('Create a product in WooCommerce that can be placed in cart.', 'bookme') ?></li>
                    <li><?php esc_html_e('In the form below enable WooCommerce option.', 'bookme') ?></li>
                    <li><?php esc_html_e('Select the product that you created at step 1 in the drop down list of products.', 'bookme') ?></li>
                    <li><?php esc_html_e('If needed, edit item data which will be displayed in the cart.', 'bookme') ?></li>
                </ol>
                <p>
                    <?php esc_html_e('Note: once you have enabled WooCommerce option the built-in payment methods will no longer work. All your customers will be redirected to WooCommerce cart for payment processing.', 'bookme') ?>
                </p>
            </div>
            <div class="form-group">
                <label for="bookme_wc_enabled"><?php esc_html_e('WooCommerce', 'bookme') ?></label>
                <div class="form-toggle-option">
                    <div>
                        <label for="bookme_wc_enabled"><?php esc_html_e('Enable', 'bookme') ?></label>
                    </div>
                    <div>
                        <input type="hidden" name="bookme_wc_enabled" value="0">
                        <label class="switch switch-sm">
                            <input name="bookme_wc_enabled" type="checkbox" id="bookme_wc_enabled"
                                   value="1" <?php checked(get_option('bookme_wc_enabled'), 1) ?>>
                            <span class="switch-state"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="bookme_wc_product"><?php esc_html_e('Booking product', 'bookme') ?></label>
                <select id="bookme_wc_product" class="form-control" name="bookme_wc_product">
                    <?php foreach ($wc_products as $item) { ?>
                        <option value="<?php echo $item['id'] ?>" <?php selected(get_option('bookme_wc_product'), $item['id']) ?>>
                            <?php echo $item['name'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="bookme_lang_wc_cart_data_name"><?php esc_html_e('Cart item data', 'bookme') ?> <i class="dashicons dashicons-editor-help"
                            title="<?php esc_attr_e('This data will be displayed in the WooCommerce cart.', 'bookme') ?>" data-tippy-placement="top"></i></label>
                <input id="bookme_lang_wc_cart_data_name" class="form-control" type="text"
                       name="bookme_lang_wc_cart_data_name"
                       value="<?php echo esc_attr(get_option('bookme_lang_wc_cart_data_name')); ?>">
            </div>
            <div class="form-group">
                <textarea class="form-control" rows="5"
                          name="bookme_lang_wc_cart_data_value"><?php echo esc_textarea(get_option('bookme_lang_wc_cart_data_value')) ?></textarea>
            </div>
            <?php
            $codes = array(
                array('code' => 'booking_date', 'description' => esc_attr__('booking date', 'bookme'),),
                array('code' => 'booking_time', 'description' => esc_attr__('booking time', 'bookme'),),
                array('code' => 'number_of_persons', 'description' => esc_attr__('number of persons', 'bookme'),),
                array('code' => 'category_name', 'description' => esc_attr__('category name', 'bookme'),),
                array('code' => 'service_name', 'description' => esc_attr__('service name', 'bookme'),),
                array('code' => 'service_price', 'description' => esc_attr__('service price', 'bookme'),),
                array('code' => 'service_info', 'description' => esc_attr__('service info', 'bookme'),),
                array('code' => 'employee_name', 'description' => esc_attr__('employee name', 'bookme'),),
                array('code' => 'employee_info', 'description' => esc_attr__('employee info', 'bookme'),)
            );
            \Bookme\Inc\Mains\Functions\System::shortcodes($codes);
            ?>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>