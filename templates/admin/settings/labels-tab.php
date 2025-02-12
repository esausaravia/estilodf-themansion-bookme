<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="labels">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Labels', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <p>
                <?php esc_html_e('Here configure the labels for the booking form.', 'bookme') ?>
            </p>
            <h5 class="m-b-20"><?php esc_html_e('Progress Bar Labels', 'bookme') ?></h5>
            <div class="form-group">
                <input id="bookme_lang_step_service" class="form-control m-b-10" type="text" name="bookme_lang_step_service" value="<?php echo esc_attr(get_option('bookme_lang_step_service')) ?>" placeholder="<?php esc_html_e('Service','bookme') ?>">
                <input id="bookme_lang_step_time" class="form-control m-b-10" type="text" name="bookme_lang_step_time" value="<?php echo esc_attr(get_option('bookme_lang_step_time')) ?>" placeholder="<?php esc_html_e('Time','bookme') ?>">
                <?php if (\Bookme\Inc\Mains\Functions\System::show_step_cart()) { ?>
                <input id="bookme_lang_step_cart" class="form-control m-b-10" type="text" name="bookme_lang_step_cart" value="<?php echo esc_attr(get_option('bookme_lang_step_cart')) ?>" placeholder="<?php esc_html_e('Cart','bookme') ?>">
                <?php } ?>
                <input id="bookme_lang_step_details" class="form-control m-b-10" type="text" name="bookme_lang_step_details" value="<?php echo esc_attr(get_option('bookme_lang_step_details')) ?>" placeholder="<?php esc_html_e('Details','bookme') ?>">
                <input id="bookme_lang_step_done" class="form-control m-b-10" type="text" name="bookme_lang_step_done" value="<?php echo esc_attr(get_option('bookme_lang_step_done')) ?>" placeholder="<?php esc_html_e('Done','bookme') ?>">
            </div>
            <div class="form-divider"></div>
            <h5 class="m-b-20"><?php esc_html_e('Form Labels', 'bookme') ?></h5>
            <div class="form-group">
                <label><?php esc_html_e('Category','bookme') ?></label>
                <input id="bookme_lang_title_category" class="form-control m-b-10" type="text" name="bookme_lang_title_category" value="<?php echo esc_attr(get_option('bookme_lang_title_category')) ?>" placeholder="<?php esc_html_e('Category','bookme') ?>">
                <input id="bookme_lang_select_category" class="form-control m-b-10" type="text" name="bookme_lang_select_category" value="<?php echo esc_attr(get_option('bookme_lang_select_category')) ?>" placeholder="<?php esc_html_e('Select Category','bookme') ?>">
            </div>
            <div class="form-group">
                <label><?php esc_html_e('Service','bookme') ?></label>
                <input id="bookme_lang_title_service" class="form-control m-b-10" type="text" name="bookme_lang_title_service" value="<?php echo esc_attr(get_option('bookme_lang_title_service')) ?>" placeholder="<?php esc_html_e('Service','bookme') ?>">
                <input id="bookme_lang_select_service" class="form-control m-b-10" type="text" name="bookme_lang_select_service" value="<?php echo esc_attr(get_option('bookme_lang_select_service')) ?>" placeholder="<?php esc_html_e('Select Service','bookme') ?>">
                <input id="bookme_lang_required_service" class="form-control m-b-10" type="text" name="bookme_lang_required_service" value="<?php echo esc_attr(get_option('bookme_lang_required_service')) ?>" placeholder="<?php esc_html_e('Please select a service','bookme') ?>">
            </div>
            <div class="form-group">
                <label><?php esc_html_e('Employee','bookme') ?></label>
                <input id="bookme_lang_title_employee" class="form-control m-b-10" type="text" name="bookme_lang_title_employee" value="<?php echo esc_attr(get_option('bookme_lang_title_employee')) ?>" placeholder="<?php esc_html_e('Employee','bookme') ?>">
                <input id="bookme_lang_select_employee" class="form-control m-b-10" type="text" name="bookme_lang_select_employee" value="<?php echo esc_attr(get_option('bookme_lang_select_employee')) ?>" placeholder="<?php esc_html_e('Select Employee','bookme') ?>">
                <input id="bookme_lang_required_employee" class="form-control m-b-10" type="text" name="bookme_lang_required_employee" value="<?php echo esc_attr(get_option('bookme_lang_required_employee')) ?>" placeholder="<?php esc_html_e('Please select an employee','bookme') ?>">
            </div>
            <div>
                <label><?php esc_html_e('Number of persons','bookme') ?></label>
                <input id="bookme_lang_title_number_of_persons" class="form-control m-b-10" type="text" name="bookme_lang_title_number_of_persons" value="<?php echo esc_attr(get_option('bookme_lang_title_number_of_persons')) ?>" placeholder="<?php esc_html_e('Number of persons','bookme') ?>">
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>