<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="general">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('General', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_time_slot_step">
                            <?php esc_html_e('Time Slot Step', 'bookme') ?> <i
                                    class="dashicons dashicons-editor-help"
                                    title="<?php esc_attr_e('This step will be used for all time slots in the plugin.', 'bookme') ?>"
                                    data-tippy-placement="top"></i>
                        </label>
                        <select id="bookme_time_slot_step" class="form-control"
                                name="bookme_time_slot_step">
                            <?php foreach (array(1, 2, 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 150, 180, 210, 240, 360, 480) as $duration) { ?>
                                <option value="<?php echo $duration ?>"
                                    <?php selected(get_option('bookme_time_slot_step'), $duration) ?>>
                                    <?php echo \Bookme\Inc\Mains\Functions\DateTime::seconds_to_interval($duration * MINUTE_IN_SECONDS) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_service_duration_as_slot_step">
                            <?php esc_html_e('Use service duration as time slot step', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Make service duration as time slot step in the booking process.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <div class="form-toggle-option" style="max-width: 100%">
                            <div>
                                <label for="bookme_service_duration_as_slot_step"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="bookme_service_duration_as_slot_step" value="0">
                                <label class="switch switch-sm">
                                    <input name="bookme_service_duration_as_slot_step" type="checkbox"
                                           id="bookme_service_duration_as_slot_step"
                                           value="1" <?php checked(get_option('bookme_service_duration_as_slot_step'), 1) ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_use_client_time_zone">
                            <?php esc_html_e("Show time slots in client's time zone", 'bookme') ?>
                        </label>
                        <div class="form-toggle-option" style="max-width: 100%">
                            <div>
                                <label for="bookme_use_client_time_zone"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="bookme_use_client_time_zone" value="0">
                                <label class="switch switch-sm">
                                    <input name="bookme_use_client_time_zone" type="checkbox"
                                           id="bookme_use_client_time_zone"
                                           value="1" <?php checked(get_option('bookme_use_client_time_zone'), 1) ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_hide_booking_end_time">
                            <?php esc_html_e("Hide booking end time", 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Hide the booking end time on the 2nd step of the booking form.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <div class="form-toggle-option" style="max-width: 100%">
                            <div>
                                <label for="bookme_hide_booking_end_time"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="bookme_hide_booking_end_time" value="0">
                                <label class="switch switch-sm">
                                    <input name="bookme_hide_booking_end_time" type="checkbox"
                                           id="bookme_hide_booking_end_time"
                                           value="1" <?php checked(get_option('bookme_hide_booking_end_time'), 1) ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_allow_staff_edit_profile">
                            <?php esc_html_e("Allow staff members to edit their profiles", 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Allow staff members with WP users to edit their profile.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <div class="form-toggle-option" style="max-width: 100%">
                            <div>
                                <label for="bookme_allow_staff_edit_profile"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="bookme_allow_staff_edit_profile" value="0">
                                <label class="switch switch-sm">
                                    <input name="bookme_allow_staff_edit_profile" type="checkbox"
                                           id="bookme_allow_staff_edit_profile"
                                           value="1" <?php checked(get_option('bookme_allow_staff_edit_profile'), 1) ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_required_employee">
                            <?php esc_html_e("Make employee required", 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Make selecting employee required in the first step of the booking form', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <div class="form-toggle-option" style="max-width: 100%">
                            <div>
                                <label for="bookme_required_employee"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="bookme_required_employee" value="0">
                                <label class="switch switch-sm">
                                    <input name="bookme_required_employee" type="checkbox"
                                           id="bookme_required_employee"
                                           value="1" <?php checked(get_option('bookme_required_employee'), 1) ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_default_booking_status">
                            <?php esc_html_e('Default Appointment Status', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('This status will be used for newly booked appointments.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select id="bookme_default_booking_status" class="form-control"
                                name="bookme_default_booking_status">
                            <option value="<?php echo \Bookme\Inc\Mains\Tables\CustomerBooking::STATUS_PENDING ?>"
                                <?php selected(get_option('bookme_default_booking_status'), \Bookme\Inc\Mains\Tables\CustomerBooking::STATUS_PENDING) ?>>
                                <?php esc_attr_e('Pending', 'bookme'); ?>
                            </option>
                            <option value="<?php echo \Bookme\Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED ?>"
                                <?php selected(get_option('bookme_default_booking_status'), \Bookme\Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED) ?>>
                                <?php esc_attr_e('Approved', 'bookme'); ?>
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_max_days_for_booking">
                            <?php esc_html_e('Number of days available for booking in advance', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Set how far customers can book in advance.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <input type="number" class="form-control" name="bookme_max_days_for_booking"
                               id="bookme_max_days_for_booking"
                               value="<?php echo esc_attr(get_option('bookme_max_days_for_booking')) ?>" min="1"
                               step="1">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_min_time_before_booking">
                            <?php esc_html_e('Minimum time requirement before booking', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Set how late appointments can be booked.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select id="bookme_min_time_before_booking" class="form-control"
                                name="bookme_min_time_before_booking">
                            <option value="0" <?php selected(get_option('bookme_min_time_before_booking'), '0') ?>><?php esc_html_e('Disabled', 'bookme') ?></option>
                            <?php foreach (array_merge(array(0.5), range(1, 12), range(24, 144, 24), range(168, 672, 168)) as $hours) { ?>
                                <option value="<?php echo $hours ?>"
                                    <?php selected(get_option('bookme_min_time_before_booking'), $hours) ?>>
                                    <?php echo \Bookme\Inc\Mains\Functions\DateTime::seconds_to_interval($hours * HOUR_IN_SECONDS) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_min_time_before_cancel">
                            <?php esc_html_e('Minimum time requirement before canceling', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Set how late appointments can be cancelled.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select id="bookme_min_time_before_cancel" class="form-control"
                                name="bookme_min_time_before_cancel">
                            <option value="0" <?php selected(get_option('bookme_min_time_before_cancel'), '0') ?>><?php esc_html_e('Disabled', 'bookme') ?></option>
                            <?php foreach (array_merge(array(0.5), range(1, 12), range(24, 144, 24), range(168, 672, 168)) as $hours) { ?>
                                <option value="<?php echo $hours ?>"
                                    <?php selected(get_option('bookme_min_time_before_cancel'), $hours) ?>>
                                    <?php echo \Bookme\Inc\Mains\Functions\DateTime::seconds_to_interval($hours * HOUR_IN_SECONDS) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_phone_default_country">
                            <?php esc_html_e('Phone field default country', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Set the default country for the phone field.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select id="bookme_phone_default_country" class="form-control"
                                name="bookme_phone_default_country">
                            <option value="disabled"
                                <?php selected(get_option('bookme_phone_default_country'), 'disabled') ?>>
                                <?php esc_attr_e('Disabled', 'bookme'); ?>
                            </option>
                            <option value="auto"
                                <?php selected(get_option('bookme_phone_default_country'), 'auto') ?>>
                                <?php esc_attr_e('Guess country by user\'s IP address', 'bookme'); ?>
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_default_country_code">
                            <?php esc_html_e('Default Country Code', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Phone numbers must have in international format for SMS notifications. So this country code will be used if a customer enters the phone number without country code.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <input id="bookme_default_country_code" class="form-control" type="number"
                               name="bookme_default_country_code"
                               value="<?php echo esc_attr(get_option('bookme_default_country_code')) ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_final_step_url">
                            <?php esc_html_e('Final step URL', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Customers will be redirected to this URL after successful booking rather than displaying the default message.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <input id="bookme_final_step_url" class="form-control" type="text" name="bookme_final_step_url"
                               value="<?php echo esc_attr(get_option('bookme_final_step_url')) ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>