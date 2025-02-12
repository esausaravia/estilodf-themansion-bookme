<?php defined('ABSPATH') or die('No script kiddies please!');// No direct access  ?>

<div class="bookme-page-wrapper">
    <header class="slidePanel-header">
        <div class="slidePanel-overlay-panel">
            <div class="slidePanel-heading">
                <h2><?php esc_html_e('Add Member', 'bookme') ?></h2>
            </div>
            <div class="slidePanel-actions">
                <button class="btn-icon btn-primary ajax-add-employee" title="<?php esc_attr_e('Save', 'bookme') ?>">
                    <i class="icon-feather-check"></i>
                </button>
                <button class="btn-icon slidePanel-close" title="<?php esc_attr_e('Close', 'bookme') ?>">
                    <i class="icon-feather-x"></i>
                </button>
            </div>
        </div>
    </header>
    <div class="slidePanel-inner">
        <div class="panel">
            <div class="panel-body">
                <form method="post" class="theme-form bm-add-employee">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group form-required">
                                <label for="bookme-full-name"><?php esc_html_e( 'Full name', 'bookme' ) ?></label>
                                <input type="text" class="form-control" id="bookme-full-name" name="full_name" value=""/>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="bookme-wp-user"><?php esc_html_e( ' WP User', 'bookme' ) ?>
                                    <i class="dashicons dashicons-editor-help"
                                       title="<?php esc_attr_e('Here you can assign a WordPress user to the staff member, if you want to give the admin access to the staff member. User with "Administrator" role will have access to all the pages and settings, user with another role will have access to only their personal settings.', 'bookme') ?>"
                                       data-tippy-placement="top"></i></label>
                                <select class="form-control" name="wp_user_id" id="bookme-wp-user">
                                    <option value=""><?php esc_attr_e( 'Select WP user', 'bookme' ) ?></option>
                                    <?php foreach ( $wp_users as $user ) { ?>
                                        <option value="<?php echo $user['ID'] ?>" data-email="<?php echo $user['user_email'] ?>"><?php echo $user['display_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="bookme-email"><?php esc_html_e( 'Email', 'bookme' ) ?></label>
                                <input class="form-control" id="bookme-email" name="email"
                                       value=""
                                       type="text"/>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="bookme-phone"><?php esc_html_e( 'Phone', 'bookme' ) ?></label>
                                <input class="form-control" id="bookme-phone" value="" type="text"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bookme-visibility"><?php esc_html_e( 'Visibility', 'bookme' ) ?></label>
                        <select name="visibility" class="form-control" id="bookme-visibility">
                            <option value="public"><?php esc_attr_e( 'Public', 'bookme' ) ?></option>
                            <option value="private"><?php esc_attr_e( 'Private', 'bookme' ) ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bookme-info"><?php esc_html_e( 'Info', 'bookme' ) ?></label>
                        <textarea id="bookme-info" name="info" rows="3" class="form-control"></textarea>
                    </div>
                    <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
                </form>
            </div>
        </div>
    </div>
</div>