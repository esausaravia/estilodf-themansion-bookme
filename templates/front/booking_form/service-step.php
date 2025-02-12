<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\Inc\Mains\Functions\System;

echo $progress_bar; ?>
<div class="bookme-booking-service-step">
    <div class="bookme-row<?php echo (int)get_option('bookme_form_layout') == 1 ? ' bookme-layout-1': ''; ?>">
        <div class="bookme-service-step-left bookme-col-md-6">
            <div class="bookme-form-group">
                <label><?php echo System::get_translated_option('bookme_lang_title_category') ?></label>
                <select class="bookme-category" name="bookme-category">
                    <option value=""><?php echo esc_html(System::get_translated_option('bookme_lang_select_category')) ?></option>
                </select>
            </div>
            <div class="bookme-form-group">
                <label><?php echo System::get_translated_option('bookme_lang_title_service') ?></label>
                <select class="bookme-service" name="bookme-service">
                    <option value=""><?php echo esc_html(System::get_translated_option('bookme_lang_select_service')) ?></option>
                </select>
                <div class="bookme-service-error bookme-form-error" style="display: none">
                    <?php echo esc_html(System::get_translated_option('bookme_lang_required_service')) ?>
                </div>
            </div>
            <div class="bookme-form-group">
                <label><?php echo System::get_translated_option('bookme_lang_title_employee') ?></label>
                <select class="bookme-employee" name="bookme-employee">
                    <option value=""><?php echo esc_html(System::get_translated_option('bookme_lang_select_employee')) ?></option>
                </select>
                <div class="bookme-employee-error bookme-form-error" style="display: none">
                    <?php echo esc_html(System::get_translated_option('bookme_lang_required_employee')) ?>
                </div>
            </div>
            <div class="bookme-form-group">
                <label><?php echo System::get_translated_option('bookme_lang_title_number_of_persons') ?></label>
                <select class="bookme-number-of-persons" name="bookme-number-of-persons">
                    <option value="1">1</option>
                </select>
            </div>
            <?php if ($show_cart) { ?>
                <button type="button" class="bookme-button bookme-icon-button bookme-cart"
                        title="<?php esc_html_e('Cart', 'bookme') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-shopping-cart">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </button>
            <?php } ?>
        </div>
        <div class="bookme-service-step-right bookme-col-md-6">
            <div class="bookme-form-group">
                <label><?php esc_html_e('Select Date', 'bookme') ?></label>
                <div>
                    <input style="display: none" class="bookme-date" type="text"
                           value="<?php echo esc_attr($user_data->get('date')) ?>">
                    <div class="bookme-calendar" data-date="<?php echo esc_attr($user_data->get('date')) ?>">
                        <!-- dynamic calendar -->
                    </div>
                </div>
            </div>
            <button type="button"
                    class="bookme-button bookme-width-full bookme-next"><?php esc_html_e('Next', 'bookme') ?></button>
        </div>
    </div>
</div>