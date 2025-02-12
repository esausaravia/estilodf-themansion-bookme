<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="company">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Company', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <div class="employee-image">
                <div class="image-box company-image-selector">
                    <?php
                    $img = wp_get_attachment_image_src(get_option('bookme_company_logo_id'));
                    $img_url = $img ? $img[0] : BOOKME_URL . '/assets/admin/images/user-default.png';
                    ?>
                    <img class="img-round"
                         src="<?php echo esc_url($img_url); ?>" alt="">
                    <i class="icon-feather-camera"></i>
                </div>
                <input type="hidden" name="bookme_company_logo_id" value="<?php echo esc_attr(get_option('bookme_company_logo_id')); ?>">
            </div>
            <div class="form-group">
                <label for="bookme_company_name"><?php esc_html_e('Company Name', 'bookme') ?></label>
                <input id="bookme_company_name" class="form-control" type="text" name="bookme_company_name" value="<?php echo esc_attr(get_option('bookme_company_name')) ?>">
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_company_phone"><?php esc_html_e('Phone', 'bookme') ?></label>
                        <input id="bookme_company_phone" class="form-control" type="text" name="bookme_company_phone" value="<?php echo esc_attr(get_option('bookme_company_phone')) ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme_company_website"><?php esc_html_e('Website', 'bookme') ?></label>
                        <input id="bookme_company_website" class="form-control" type="text" name="bookme_company_website" value="<?php echo esc_attr(get_option('bookme_company_website')) ?>">
                    </div>
                </div>
            </div>
            <div class="form-group m-b-0">
                <label for="bookme_company_address"><?php esc_html_e('Address', 'bookme') ?></label>
                <textarea id="bookme_company_address" class="form-control" rows="3"
                          name="bookme_company_address"><?php echo esc_textarea(get_option('bookme_company_address')) ?></textarea>
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>