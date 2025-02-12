<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\Inc\Mains\Functions\Price;
use Bookme\Inc\Mains\Functions\System;
use Bookme\Inc\Mains\Functions\DateTime;

if (trim($custom_css)) { ?>
    <style type="text/css">
        <?php echo $custom_css ?>
    </style>
<?php } ?>
<div class="bookme-booking-form">
    <ul class="bookme-steps">
        <li class="bookme-steps-is-active">
            <span><?php echo System::get_translated_option('bookme_lang_step_service') ?></span>
        </li>
        <li class="bookme-steps-is-active">
            <span><?php echo System::get_translated_option('bookme_lang_step_time') ?></span>
        </li>
        <?php if (System::show_step_cart()) { ?>
            <li class="bookme-steps-is-active">
                <span><?php echo System::get_translated_option('bookme_lang_step_cart') ?></span>
            </li>
        <?php } ?>
        <li class="bookme-steps-is-active">
            <span><?php echo System::get_translated_option('bookme_lang_step_details') ?></span>
        </li>
        <li class="bookme-steps-is-active">
            <span><?php echo System::get_translated_option('bookme_lang_step_done') ?></span>
        </li>
    </ul>
    <div class="bookme-booking-service-step">
        <div class="bookme-row">
            <div class="bookme-service-step-left bookme-col-md-6">
                <div class="bookme-form-group">
                    <label><?php echo System::get_translated_option('bookme_lang_title_category') ?></label>
                    <select class="bookme-category" name="bookme-category">
                        <option value=""><?php echo esc_html(System::get_translated_option('bookme_lang_select_category')) ?></option>
                        <option value="1">Back Pain and Neck Pain</option>
                        <option value="2">Ankle Pain</option>
                        <option value="3">Hand Pain and Hand Injury</option>
                        <option value="4">Foot Pain and Foot Injury</option>
                    </select>
                </div>
                <div class="bookme-form-group">
                    <label><?php echo System::get_translated_option('bookme_lang_title_service') ?></label>
                    <select class="bookme-service" name="bookme-service">
                        <option value=""><?php echo esc_html(System::get_translated_option('bookme_lang_select_service')) ?></option>
                        <option value="1" class="service-with-duration">Back Pain Treatment
                            (<?php echo DateTime::seconds_to_interval(3600) ?>)
                        </option>
                        <option value="-1" class="service-without-duration">Back Pain Treatment</option>
                        <option value="2" class="service-with-duration">Spine Cancer
                            (<?php echo DateTime::seconds_to_interval(3600 * 2) ?>)
                        </option>
                        <option value="-2" class="service-without-duration">Spine Cancer</option>
                        <option value="3" class="service-with-duration">Spine Fracture
                            (<?php echo DateTime::seconds_to_interval(3600 * 12) ?>)
                        </option>
                        <option value="-3" class="service-without-duration">Spine Fracture</option>
                        <option value="4" class="service-with-duration">Scoliosis Treatment
                            (<?php echo DateTime::seconds_to_interval(3600 * 24) ?>)
                        </option>
                        <option value="-4" class="service-without-duration">Scoliosis Treatment</option>
                        <option value="5" class="service-with-duration">Ankle Arthroscopy
                            (<?php echo DateTime::seconds_to_interval(3600 * 8) ?>)
                        </option>
                        <option value="-5" class="service-without-duration">Ankle Arthroscopy</option>
                        <option value="6" class="service-with-duration">Ankle Replacement Surgery
                            (<?php echo DateTime::seconds_to_interval(3600 * 6) ?>)
                        </option>
                        <option value="-6" class="service-without-duration">Ankle Replacement Surgery</option>
                        <option value="7" class="service-with-duration">Ankle Fusion
                            (<?php echo DateTime::seconds_to_interval(3600 * 16) ?>)
                        </option>
                        <option value="-7" class="service-without-duration">Ankle Fusion</option>
                    </select>
                </div>
                <div class="bookme-form-group">
                    <label><?php echo System::get_translated_option('bookme_lang_title_employee') ?></label>
                    <select class="bookme-employee" name="bookme-employee">
                        <option value=""><?php echo esc_html(System::get_translated_option('bookme_lang_select_employee')) ?></option>
                        <option value="1" class="staff-with-price">Laura White
                            (<?php echo Price::format(250) ?>)
                        </option>
                        <option value="-1" class="staff-without-price">Laura White</option>
                        <option value="2" class="staff-with-price">Adam Footman
                            (<?php echo Price::format(300) ?>)
                        </option>
                        <option value="-2" class="staff-without-price">Adam Footman</option>
                        <option value="3" class="staff-with-price">Magen Granger
                            (<?php echo Price::format(350) ?>)
                        </option>
                        <option value="-3" class="staff-without-price">Magen Granger</option>
                        <option value="4" class="staff-with-price">Max Plank
                            (<?php echo Price::format(400) ?>)
                        </option>
                        <option value="-4" class="staff-without-price">Max Plank</option>
                        <option value="5" class="staff-with-price">Lisa Hil
                            (<?php echo Price::format(450) ?>)
                        </option>
                        <option value="-5" class="staff-without-price">Lisa Hil</option>
                    </select>
                </div>
                <div class="bookme-form-group">
                    <label><?php echo System::get_translated_option('bookme_lang_title_number_of_persons') ?></label>
                    <select class="bookme-number-of-persons" name="bookme-number-of-persons">
                        <?php for($i = 1; $i <= 10; $i++){ ?>
                        <option value="<?php echo $i ?>"><?php echo $i ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="bookme-service-step-right bookme-col-md-6">
                <div class="bookme-form-group">
                    <label><?php esc_html_e('Select Date', 'bookme') ?></label>
                    <div>
                        <div class="bookme-calendar">
                            <!-- dynamic calendar -->
                        </div>
                    </div>
                </div>
                <button type="button"
                        class="bookme-button bookme-width-full bookme-next"><?php esc_html_e('Next', 'bookme') ?></button>
            </div>
        </div>
    </div>
</div>
<div id="bm-js-style"></div>