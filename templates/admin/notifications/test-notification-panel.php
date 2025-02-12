<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>

<div id="bookme-test-notifications-panel"
     class="slidePanel <?php echo is_rtl() ? 'slidePanel-left' : 'slidePanel-right'; ?>">
    <div class="slidePanel-scrollable">
        <div>
            <div class="slidePanel-content">
                <header class="slidePanel-header">
                    <div class="slidePanel-overlay-panel">
                        <div class="slidePanel-heading">
                            <h2><?php esc_html_e('Test Email Notifications', 'bookme') ?></h2>
                        </div>
                        <div class="slidePanel-actions">
                            <div class="btn-group-flat">
                                <button class="btn-icon btn-primary ajax-send-notifications"
                                        title="<?php esc_attr_e('Save', 'bookme') ?>">
                                    <i class="icon-feather-check"></i>
                                </button>
                                <button class="btn-icon slidePanel-close"
                                        title="<?php esc_attr_e('Close', 'bookme') ?>">
                                    <i class="icon-feather-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="slidePanel-inner">
                    <form method="post" class="theme-form" onsubmit="return false">
                        <div class="form-group form-required">
                            <label for="recipient_email"><?php esc_html_e('Recipient Email', 'bookme') ?></label>
                            <input id="recipient_email" name="recipient_email" class="form-control" type="text"
                                   value=""/>
                        </div>
                        <div class="form-group">
                            <label><?php esc_html_e('Notifications', 'bookme') ?></label>
                            <select name="notifications[]" id="bookme-test-notification" class="form-control"
                                    multiple
                                    data-placeholder="<?php esc_attr_e('Select Notifications', 'bookme') ?>"
                                    data-nothing="<?php esc_attr_e('No selected', 'bookme') ?>"
                                    data-selected="<?php esc_attr_e('selected', 'bookme') ?>"
                                    data-selectall="<?php esc_attr_e('Select All', 'bookme') ?>"
                                    data-unselectall="<?php esc_attr_e('Unselect All', 'bookme') ?>"
                                    data-allselected="<?php esc_attr_e('All Notifications Selected', 'bookme') ?>">
                                <?php foreach ($test_notifications as $notification) { ?>
                                    <option value="<?php echo $notification['id'] ?>" selected>
                                        <?php echo esc_attr($notification['name']) ?>
                                    </option>
                                <?php } ?>

                            </select>
                        </div>
                        <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>