<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="online_meeting">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Zoom', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <div class="form-group">
                <h5 class="bookme-bold"><?php _e('Instructions', 'bookme') ?></h5>
                <p><?php _e('To find your API Key and API Secret, follow the below steps:', 'bookme') ?></p>
                <ol>
                    <li><?php _e('Go to the <a href="https://marketplace.zoom.us/" target="_blank">Zoom Marketplace</a> and sign in.', 'bookme') ?></li>
                    <li><?php _e('Click on the <strong>Develop</strong> dropdown menu and select <strong>Build APP</strong>.', 'bookme') ?></li>
                    <li><?php _e('Select <strong>JWT</strong> as the app type.', 'bookme') ?></li>
                    <li><?php _e('After creating your app, Fill in your APP information.', 'bookme') ?></li>
                    <li><?php _e('Go to App Credentials tab and look for the <strong>API Key</strong> and <strong>API Secret</strong>. Use them in the below form.', 'bookme') ?></li>
                    <li><?php _e('After that, go to Activation tab and make sure your app is activated.', 'bookme') ?></li>
                    <li><?php _e('Go to <strong>Bookme</strong> > <strong>Services</strong>; edit a service and enable <strong>Zoom</strong> for the service.', 'bookme') ?></li>
                </ol>
            </div>
            <div class="form-group">
                <label for="bookme_zoom_enabled">
                    <?php esc_html_e('Zoom', 'bookme') ?>
                </label>
                <div class="form-toggle-option">
                    <div>
                        <label for="bookme_zoom_enabled"><?php esc_html_e('Enable', 'bookme') ?></label>
                    </div>
                    <div>
                        <input type="hidden" name="bookme_zoom_enabled" value="0">
                        <label class="switch switch-sm">
                            <input name="bookme_zoom_enabled" type="checkbox"
                                   id="bookme_zoom_enabled"
                                   value="1" <?php checked(get_option('bookme_zoom_enabled'), 1) ?>>
                            <span class="switch-state"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="bookme_zoom_api_key"><?php esc_html_e('API Key', 'bookme') ?></label>
                <input class="form-control" id="bookme_zoom_api_key" type="text" name="bookme_zoom_api_key" value="<?php echo esc_attr(get_option('bookme_zoom_api_key')) ?>">
            </div>
            <div class="form-group">
                <label for="bookme_zoom_api_secret"><?php esc_html_e('API Secret', 'bookme') ?></label>
                <input class="form-control" id="bookme_zoom_api_secret" type="text" name="bookme_zoom_api_secret" value="<?php echo esc_attr(get_option('bookme_zoom_api_secret')) ?>">
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>