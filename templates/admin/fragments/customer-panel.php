<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<div id="bm-customer-sidepanel" class="slidePanel <?php echo is_rtl() ? 'slidePanel-left' : 'slidePanel-right'; ?>">
    <div class="slidePanel-scrollable">
        <div>
            <div class="slidePanel-content">
                <header class="slidePanel-header">
                    <div class="slidePanel-overlay-panel">
                        <div class="slidePanel-heading">
                            <h2 id="bm-add-customer-title"><?php esc_html_e('Add Customer', 'bookme'); ?></h2>
                            <h2 id="bm-edit-customer-title"><?php esc_html_e('Edit Customer', 'bookme'); ?></h2>
                        </div>
                        <div class="slidePanel-actions">
                            <button id="ajax-save-customer" class="btn-icon btn-primary"
                                    title="<?php esc_attr_e('Save', 'bookme') ?>">
                                <i class="icon-feather-check"></i>
                            </button>
                            <button class="btn-icon slidePanel-close" title="<?php esc_attr_e('Close', 'bookme') ?>">
                                <i class="icon-feather-x"></i>
                            </button>
                        </div>
                    </div>
                </header>
                <div class="slidePanel-inner">
                    <form class="theme-form">
                        <div class="form-group">
                            <label for="bm-wp-user"><?php esc_html_e('WP User', 'bookme') ?></label>
                            <select class="form-control" id="bm-wp-user" name="wp_user_id">
                                <option value=""></option>
                                <?php foreach (get_users(array('fields' => array('ID', 'display_name'), 'orderby' => 'display_name')) as $wp_user) { ?>
                                    <option value="<?php echo $wp_user->ID ?>">
                                        <?php echo esc_attr($wp_user->display_name) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php if (\Bookme\Inc\Mains\Functions\System::show_first_last_name()) { ?>
                            <div class="form-group form-required">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label for="bm-first-name"><?php esc_html_e('First name', 'bookme') ?></label>
                                        <input class="form-control" type="text" id="bm-first-name" name="first_name"/>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="bm-last-name"><?php esc_html_e('Last name', 'bookme') ?></label>
                                        <input class="form-control" type="text" id="bm-last-name" name="last_name"/>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="form-group form-required">
                                <label for="bm-full-name"><?php esc_html_e('Name', 'bookme') ?></label>
                                <input class="form-control" type="text" id="bm-full-name" name="full_name"/>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="bm-email"><?php esc_html_e('Email', 'bookme') ?></label>
                                    <input class="form-control" type="text" id="bm-email" name="email"/>
                                </div>
                                <div class="col-sm-6">
                                    <label for="bm-phone"><?php esc_html_e('Phone', 'bookme') ?></label>
                                    <input class="form-control" type="text" id="bm-phone" name="phone"/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="bm-notes"><?php esc_html_e('Notes', 'bookme') ?></label>
                            <textarea class="form-control" id="bm-notes" name="notes"></textarea>
                        </div>
                        <input type="hidden" name="id" id="bm-customer-id" value="">
                        <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>