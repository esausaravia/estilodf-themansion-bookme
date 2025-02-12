<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="notifications">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Notifications', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <h5 class="m-b-20"><?php esc_html_e('Email Notifications', 'bookme') ?></h5>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_email_sender_name"><?php _e('Sender name', 'bookme') ?></label>
                        <input id="bookme_email_sender_name" name="bookme_email_sender_name"
                               class="form-control"
                               type="text"
                               value="<?php echo esc_attr(get_option('bookme_email_sender_name') == '' ?
                                   get_option('blogname') : get_option('bookme_email_sender_name')) ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_email_sender"><?php _e('Sender email', 'bookme') ?></label>
                        <input id="bookme_email_sender" name="bookme_email_sender"
                               class="form-control" type="text"
                               value="<?php echo esc_attr(get_option('bookme_email_sender') == '' ?
                                   get_option('admin_email') : get_option('bookme_email_sender')) ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_email_send_as">
                            <?php esc_html_e('Send emails as', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('HTML allows formatting, fonts, colors etc. With text you must use the text-mode of the editor below.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select class="form-control" name="bookme_email_send_as" id="bookme_email_send_as">
                            <option value="html" <?php selected(get_option('bookme_email_send_as'),'html') ?>><?php esc_html_e('HTML', 'bookme') ?></option>
                            <option value="text" <?php selected(get_option('bookme_email_send_as'),'text') ?>><?php esc_html_e('Text', 'bookme') ?></option>
                        </select></div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_email_reply_to_customers">
                            <?php esc_html_e('Reply directly to customers', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('If this option is enabled, then staff members can directly reply to the email, to reply to the customer.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <div class="form-toggle-option" style="max-width: 100%">
                            <div>
                                <label for="bookme_email_reply_to_customers"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="bookme_email_reply_to_customers" value="0">
                                <label class="switch switch-sm">
                                    <input name="bookme_email_reply_to_customers" type="checkbox"
                                           id="bookme_email_reply_to_customers"
                                           value="1" <?php checked(get_option('bookme_email_reply_to_customers'), 1) ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-divider m-t-10"></div>
            <h5 class="m-b-20"><?php esc_html_e('SMS Notifications', 'bookme') ?></h5>
            <div class="alert alert-info">
                <?php printf(__('Bookme uses <a href="%s" target="_blank" class="alert-link">Twilio SMS API</a> for sending SMS. Just follow the below steps and enjoy the SMS service.', 'bookme'), 'https://www.twilio.com/'); ?>
            </div>
            <ol>
                <li><?php printf(__('Visit to <a href="%s" target="_blank">Twilio website</a> and sign up or log in.', 'bookme'), 'https://www.twilio.com/'); ?></li>
                <li><?php printf(__('After that go to <a href="%s" target="_blank">Console page.</a>', 'bookme'), 'https://www.twilio.com/console'); ?></li>
                <li><?php esc_html_e('And now copy ACCOUNT SID and AUTH TOKEN and paste below.', 'bookme') ?></li>
                <li><?php esc_html_e('After that create Twilio phone number and paste below.', 'bookme') ?></li>
                <li><?php esc_html_e("That's it. Now enjoy the SMS service.", 'bookme') ?></li>
            </ol>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_twillio_account_sid">
                            <?php esc_html_e('Account SID', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Get Twilio Account SID from Twilio website.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <input id="bookme_twillio_account_sid" name="bookme_twillio_account_sid"
                               class="form-control" type="text"
                               value="<?php echo esc_attr( get_option('bookme_twillio_account_sid')) ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_twillio_auth_token">
                            <?php esc_html_e('Auth Token', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Get Twilio Auth Token from Twilio website', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <input id="bookme_twillio_auth_token" name="bookme_twillio_auth_token"
                               class="form-control" type="text"
                               value="<?php echo esc_attr( get_option('bookme_twillio_auth_token')) ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label for="bookme_twillio_phone_number">
                        <?php esc_html_e('Twilio Phone Number', 'bookme') ?>
                        <i class="dashicons dashicons-editor-help"
                           title="<?php esc_attr_e('Get Twilio Phone number from Twilio website', 'bookme') ?>"
                           data-tippy-placement="top"></i>
                    </label>
                    <input id="bookme_twillio_phone_number" name="bookme_twillio_phone_number"
                           class="form-control" type="text"
                           value="<?php echo esc_attr( get_option('bookme_twillio_phone_number')) ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label for="bookme_sms_admin_phone">
                        <?php esc_html_e('Admin Phone Number', 'bookme') ?>
                        <i class="dashicons dashicons-editor-help"
                           title="<?php esc_attr_e('Admin phone number for SMS notification.', 'bookme') ?>"
                           data-tippy-placement="top"></i>
                    </label>
                    <input id="bookme_sms_admin_phone" name="bookme_sms_admin_phone"
                           class="form-control" type="text"
                           value="<?php echo esc_attr( get_option('bookme_sms_admin_phone')) ?>">
                    </div>
                </div>
            </div>
            <div class="form-divider m-t-10"></div>
            <div class="form-group">
                <label for="bookme_combined_notifications">
                    <?php esc_html_e('Combined notifications', 'bookme') ?>
                    <i class="dashicons dashicons-editor-help"
                       title="<?php esc_attr_e('Send a single notification for entire booking rather than separate notifications for each appointment (e.g. when cart is enabled).', 'bookme') ?>"
                       data-tippy-placement="top"></i>
                </label>
                <div class="form-toggle-option">
                    <div>
                        <label for="bookme_combined_notifications"><?php esc_html_e('Enable', 'bookme') ?></label>
                    </div>
                    <div>
                        <input type="hidden" name="bookme_combined_notifications" value="0">
                        <label class="switch switch-sm">
                            <input name="bookme_combined_notifications" type="checkbox"
                                   id="bookme_combined_notifications"
                                   value="1" <?php checked(get_option('bookme_combined_notifications'), 1) ?>>
                            <span class="switch-state"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="bookme_approve_success_url">
                    <?php esc_html_e('Approve appointment redirect URL (successful)', 'bookme') ?>
                    <i class="dashicons dashicons-editor-help"
                       title="<?php esc_attr_e('URL on which staff member will be redirected when the appointment is approved successfully.', 'bookme') ?>"
                       data-tippy-placement="top"></i>
                </label>
                <input id="bookme_approve_success_url" class="form-control" type="text"
                       name="bookme_approve_success_url"
                       value="<?php echo esc_attr(get_option('bookme_approve_success_url')) ?>">
            </div>
            <div class="form-group">
                <label for="bookme_approve_unsuccess_url">
                    <?php esc_html_e('Approve appointment redirect URL (unsuccessful)', 'bookme') ?>
                    <i class="dashicons dashicons-editor-help"
                       title="<?php esc_attr_e('URL on which staff member will be redirected when the appointment is not approved.', 'bookme') ?>"
                       data-tippy-placement="top"></i>
                </label>
                <input id="bookme_approve_unsuccess_url" class="form-control" type="text"
                       name="bookme_approve_unsuccess_url"
                       value="<?php echo esc_attr(get_option('bookme_approve_unsuccess_url')) ?>">
            </div>
            <div class="form-group">
                <label for="bookme_cancel_success_url">
                    <?php esc_html_e('Cancel appointment redirect URL (successful)', 'bookme') ?>
                    <i class="dashicons dashicons-editor-help"
                       title="<?php esc_attr_e('URL on which customer will be redirected when the appointment is cancelled successfully.', 'bookme') ?>"
                       data-tippy-placement="top"></i>
                </label>
                <input id="bookme_cancel_success_url" class="form-control" type="text"
                       name="bookme_cancel_success_url"
                       value="<?php echo esc_attr(get_option('bookme_cancel_success_url')) ?>">
            </div>
            <div class="form-group">
                <label for="bookme_cancel_unsuccess_url">
                    <?php esc_html_e('Cancel appointment redirect URL (unsuccessful)', 'bookme') ?>
                    <i class="dashicons dashicons-editor-help"
                       title="<?php esc_attr_e('URL on which customer will be redirected when the appointment is not cancelled because of "minimum time required for canceling" value.', 'bookme') ?>"
                       data-tippy-placement="top"></i>
                </label>
                <input id="bookme_cancel_unsuccess_url" class="form-control" type="text"
                       name="bookme_cancel_unsuccess_url"
                       value="<?php echo esc_attr(get_option('bookme_cancel_unsuccess_url')) ?>">
            </div>
            <div class="form-group">
                <label for="bookme_reject_success_url">
                    <?php esc_html_e('Reject appointment redirect URL (successful)', 'bookme') ?>
                    <i class="dashicons dashicons-editor-help"
                       title="<?php esc_attr_e('URL on which staff member will be redirected when the appointment is rejected successfully.', 'bookme') ?>"
                       data-tippy-placement="top"></i>
                </label>
                <input id="bookme_reject_success_url" class="form-control" type="text"
                       name="bookme_reject_success_url"
                       value="<?php echo esc_attr(get_option('bookme_reject_success_url')) ?>">
            </div>
            <div>
                <label for="bookme_reject_unsuccess_url">
                    <?php esc_html_e('Reject appointment redirect URL (unsuccessful)', 'bookme') ?>
                    <i class="dashicons dashicons-editor-help"
                       title="<?php esc_attr_e('URL on which customer will be redirected when the appointment is not rejected.', 'bookme') ?>"
                       data-tippy-placement="top"></i>
                </label>
                <input id="bookme_reject_unsuccess_url" class="form-control" type="text"
                       name="bookme_reject_unsuccess_url"
                       value="<?php echo esc_attr(get_option('bookme_reject_unsuccess_url')) ?>">
            </div>

        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>