<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="google_recaptcha">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Google reCAPTCHA', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <div class="form-group">
                <h5 class="bookme-bold"><?php _e('Instructions', 'bookme') ?></h5>
                <p><?php _e('To find your Site Key and Secret Key, follow the below steps:', 'bookme') ?></p>
                <ol>
                    <li><?php _e('Go to the <a href="https://www.google.com/recaptcha/admin/create" target="_blank">Google reCAPTCHA</a> and register a new site.', 'bookme') ?></li>
                    <li><?php _e('Enter label and select <strong>reCAPTCHA v2</strong> -> <strong>"I\'m not a robot" Checkbox</strong> in <strong>reCAPTCHA type</strong> field.', 'bookme') ?></li>
                    <li><?php _e('Enter your domain url.', 'bookme') ?></li>
                    <li><?php _e('Accept Terms of Service and click on the <strong>Submit</strong> button.', 'bookme') ?></li>
                    <li><?php _e('Look for the <strong>Site Key</strong> and <strong>Secret Key</strong>. Use them in the form below on this page.', 'bookme') ?></li>
                    <li><?php _e('Enable Google reCAPTCHA and click on the <strong>Save</strong> button.', 'bookme') ?></li>
                </ol>
            </div>
            <div class="form-group">
                <label for="bookme_captcha_enabled">
                    <?php esc_html_e('Google reCAPTCHA', 'bookme') ?>
                </label>
                <div class="form-toggle-option">
                    <div>
                        <label for="bookme_captcha_enabled"><?php esc_html_e('Enable', 'bookme') ?></label>
                    </div>
                    <div>
                        <input type="hidden" name="bookme_captcha_enabled" value="0">
                        <label class="switch switch-sm">
                            <input name="bookme_captcha_enabled" type="checkbox"
                                   id="bookme_captcha_enabled"
                                   value="1" <?php checked(get_option('bookme_captcha_enabled'), 1) ?>>
                            <span class="switch-state"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="bookme_captcha_site_key"><?php esc_html_e('Site Key', 'bookme') ?></label>
                <input class="form-control" id="bookme_captcha_site_key" type="text" name="bookme_captcha_site_key" value="<?php echo esc_attr(get_option('bookme_captcha_site_key')) ?>">
            </div>
            <div class="form-group">
                <label for="bookme_captcha_secret_key"><?php esc_html_e('Secret Key', 'bookme') ?></label>
                <input class="form-control" id="bookme_captcha_secret_key" type="text" name="bookme_captcha_secret_key" value="<?php echo esc_attr(get_option('bookme_captcha_secret_key')) ?>">
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>