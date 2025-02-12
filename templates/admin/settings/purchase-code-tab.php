<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="purchase_code">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Purchase Code', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <?php
            if (($already_file = get_option('bookme_secret_file')) && get_option('bookme_purchase_code')) {
                if (file_exists(BOOKME_PATH . '/templates/admin/employees/' . $already_file . '.php')) {
                    ?>
                    <div class="alert alert-info">
                        <?php esc_html_e('Your purchase code is already verified.', 'bookme'); ?>
                    </div>
                    <?php
                }
            }
            ?>
            <div class="form-group">
                <label for="bookme_purchase_code"><?php esc_html_e('Purchase Code', 'bookme') ?></label>
                <input id="bookme_purchase_code" class="form-control" type="text" name="bookme_purchase_code">
            </div>
            <p><a href='https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-' target="_blank"><?php esc_html_e("Don't know your purchase code? Find here.", 'bookme') ?></a></p>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>