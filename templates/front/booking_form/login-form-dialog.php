<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<div class="bookme-modal bookme-login-modal bookme-modal-hide">
    <div class="bookme-modal-dialog bookme-booking-form">
        <div class="bookme-modal-content">
            <div class="bookme-modal-header">
                <div><?php esc_html_e('Login', 'bookme') ?><button type="button" class="bookme-modal-close bookme-modal-dismiss">×</button></div>
            </div>
            <form>
            <div class="bookme-modal-body">
                    <div class="bookme-form-group">
                        <label><?php esc_html_e('Username') ?></label>
                        <input type="text" name="log" required/>
                    </div>
                    <div class="bookme-form-group">
                        <label><?php esc_html_e('Password') ?></label>
                        <input type="password" name="pwd" required/>
                        <div class="bookme-form-error"></div>
                    </div>
                    <div class="bookme-login-modal-extra">
                        <div>
                            <label>
                                <input type="checkbox" name="rememberme"/>
                                <span><?php esc_html_e('Remember Me') ?></span>
                            </label>
                        </div>
                        <div>
                            <a class="bookme-left"
                               href="<?php echo esc_url(wp_lostpassword_url()) ?>"
                               target="_blank"><?php esc_html_e('Lost your password?') ?></a>
                        </div>
                    </div>
            </div>
                <div class="bookme-modal-actions">
                    <button class="bookme-button bookme-modal-submit"
                            type="submit"><?php esc_html_e('Log In') ?></button>
                    <a class="bookme-modal-cancel bookme-modal-dismiss" href="#"
                       type="button"><?php esc_html_e('Cancel') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>