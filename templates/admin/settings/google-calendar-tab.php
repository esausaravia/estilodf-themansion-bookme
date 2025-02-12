<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="google_calendar">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Google Calendar', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <div class="form-group">
                <h5 class="bookme-bold"><?php _e('Instructions', 'bookme') ?></h5>
                <p><?php _e('To find your client ID and client secret, follow the below steps:', 'bookme') ?></p>
                <ol>
                    <li><?php _e('Go to the <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a>.', 'bookme') ?></li>
                    <li><?php _e('Select a project, or create a new one.', 'bookme') ?></li>
                    <li><?php _e('Click in the upper left part to see a sliding sidebar. Next, click <strong>APIs & Services</strong> -> <strong>Library</strong>. In the list of APIs look for <strong>Calendar API</strong> and make sure it is enabled.', 'bookme') ?></li>
                    <li><?php _e('In the sidebar on the left, select <strong>APIs & Services</strong> -> <strong>Credentials</strong>.', 'bookme') ?></li>
                    <li><?php _e('Go to <strong>OAuth consent screen</strong> tab and give a name to the product, then click <strong>Save</strong>.', 'bookme') ?></li>
                    <li><?php _e('Go to <strong>Credentials</strong> tab and in <strong>New credentials</strong> drop-down menu select <strong>OAuth client ID</strong>.', 'bookme') ?></li>
                    <li><?php _e('Select <strong>Web application</strong> and create your project\'s OAuth 2.0 credentials by providing the necessary information. For <strong>Authorized redirect URIs</strong> enter the <strong>Redirect URI</strong> found below on this page. Click <strong>Create</strong>.', 'bookme') ?></li>
                    <li><?php _e('In the popup window look for the <strong>Client ID</strong> and <strong>Client secret</strong>. Use them in the form below on this page.', 'bookme') ?></li>
                    <li><?php _e('Go to Staff Members, edit a staff member and click <strong>Connect</strong> which is located at the bottom of the sidepanel.', 'bookme') ?></li>
                </ol>
            </div>
            <div class="form-group">
                <label for="bookme_gc_client_id"><?php esc_html_e('Client ID', 'bookme') ?></label>
                <input class="form-control" id="bookme_gc_client_id" type="text" name="bookme_gc_client_id" value="<?php echo esc_attr(get_option('bookme_gc_client_id')) ?>">
            </div>
            <div class="form-group">
                <label for="bookme_gc_client_secret"><?php esc_html_e('Client Secret', 'bookme') ?></label>
                <input class="form-control" id="bookme_gc_client_secret" type="text" name="bookme_gc_client_secret" value="<?php echo esc_attr(get_option('bookme_gc_client_secret')) ?>">
            </div>
            <div class="form-group">
                <label for="bookme_redirect_uri"><?php esc_html_e('Redirect URI', 'bookme') ?> <i class="dashicons dashicons-editor-help" title="<?php esc_attr_e('Use this redirect url in Google Console.', 'bookme') ?>" data-tippy-placement="top"></i></label>
                <input id="bookme_redirect_uri" class="form-control" type="text" value="<?php echo \Bookme\Inc\Mains\Google::generate_redirect_uri() ?>" readonly>
            </div>
            <div class="form-group">
                <label for="bookme_gc_remove_busy_slots"><?php esc_html_e('Remove Google Calendar Busy Slots', 'bookme') ?> <i class="dashicons dashicons-editor-help" title="<?php esc_attr_e("Enable this option if you want to remove Google calendar busy time slots from employee's free schedule (in the second step of the booking form)", 'bookme') ?>" data-tippy-placement="top"></i></label>
                <div class="form-toggle-option">
                    <div>
                        <label for="bookme_gc_remove_busy_slots"><?php esc_html_e('Enable', 'bookme') ?></label>
                    </div>
                    <div>
                        <input type="hidden" name="bookme_gc_remove_busy_slots" value="0">
                        <label class="switch switch-sm">
                            <input name="bookme_gc_remove_busy_slots" type="checkbox" id="bookme_gc_remove_busy_slots" value="1" <?php checked(get_option('bookme_gc_remove_busy_slots'), 1) ?>>
                            <span class="switch-state"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="bookme_gc_limit_events"><?php esc_html_e('Maximum Number Of Events Fetched', 'bookme') ?> <i class="dashicons dashicons-editor-help" title="<?php esc_attr_e("Specify the maximum number of fetched events from Google Calendar. It is recommended to use a smaller number because it will affect the form loading speed.", 'bookme') ?>" data-tippy-placement="top"></i></label>
                <select class="form-control" name="bookme_gc_limit_events" id="bookme_gc_limit_events">
                    <option value="25" <?php selected(get_option('bookme_gc_limit_events'), 25) ?>>25</option>
                    <option value="50" <?php selected(get_option('bookme_gc_limit_events'), 50) ?>>50</option>
                    <option value="100" <?php selected(get_option('bookme_gc_limit_events'), 100) ?>>100</option>
                    <option value="250" <?php selected(get_option('bookme_gc_limit_events'), 250) ?>>250</option>
                    <option value="500" <?php selected(get_option('bookme_gc_limit_events'), 500) ?>>500</option>
                    <option value="1000" <?php selected(get_option('bookme_gc_limit_events'), 1000) ?>>1000</option>
                    <option value="2000" <?php selected(get_option('bookme_gc_limit_events'), 2000) ?>>2000</option>
                    <option value="2500" <?php selected(get_option('bookme_gc_limit_events'), 2500) ?>>2500</option>
                </select>
            </div>
            <div class="form-group">
                <label for="bookme_gc_event_title"><?php esc_html_e('Event Title', 'bookme') ?> <i class="dashicons dashicons-editor-help" title="<?php esc_attr_e("Title of Google Calendar event.", 'bookme') ?>" data-tippy-placement="top"></i></label>
                <input id="bookme_gc_event_title" class="form-control" type="text" name="bookme_gc_event_title" value="<?php echo esc_attr(get_option('bookme_gc_event_title')) ?>">
            </div>
            <?php
            $codes = array(
                array('code' => 'category_name', 'description' => esc_attr__('category name', 'bookme'),),
                array('code' => 'service_name', 'description' => esc_attr__('service name', 'bookme'),),
                array('code' => 'employee_name', 'description' => esc_attr__('employee name', 'bookme'),),
                array('code' => 'customer_name', 'description' => esc_attr__('customer name', 'bookme'),)
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