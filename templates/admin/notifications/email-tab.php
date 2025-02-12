<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\Inc\Mains\Tables\Notification;

$cron_reminder = (array)get_option('bookme_cron_times');

$test_notifications = array();
?>
<form class="theme-form bm-ajax-form" method="post" data-tab="email">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Email Notifications', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <?php printf(__('To configure your email notification settings, <a href="%s" class="alert-link">click here</a>.', 'bookme'),
                    \Bookme\Inc\Mains\Functions\System::esc_admin_url(\Bookme\App\Admin\Settings::page_slug,array('tab'=>'notifications'))
                    ); ?>
            </div>
            <div class="bm-accordion" id="accordion">
                <div class="m-b-30">
                    <h5 class="m-b-20"><?php esc_html_e('To Customers', 'bookme') ?></h5>
                    <?php
                    if (!empty($notifications['combined'])) { ?>
                        <div class="m-b-20"><strong><?php esc_html_e('Single Notifications', 'bookme') ?></strong></div>
                    <?php }
                    foreach ($notifications['single'] as $notification) {
                        if ($notification['to_customer']) {
                            $id = $notification['id'];
                            $name = Notification::get_name($notification['type']);
                            $test_notifications[] = array('id' => $id, 'name' => esc_html__('Customer', 'bookme').' '.$name);
                            ?>
                            <div class="card bookme-card">
                                <div class="card-header d-flex align-items-center">
                                    <input name="notification[<?php echo $id ?>][active]" value="0"
                                           type="hidden">
                                    <label class="switch switch-sm m-r-10">
                                        <input name="notification[<?php echo $id ?>][active]" type="checkbox"
                                               id="active_<?php echo $id ?>"
                                               value="1" <?php checked($notification['active']) ?>>
                                        <span class="switch-state"></span>
                                    </label>
                                    <h5 class="mb-0">
                                        <button class="btn btn-link pl-0" data-toggle="collapse"
                                                data-target="#notification_<?php echo $id ?>"
                                                aria-expanded="false" aria-controls="notification_<?php echo $id ?>"
                                                type="button">
                                            <?php echo esc_html($name);
                                            if (in_array($notification['type'], array('client_follow_up', 'client_reminder'))) { ?>
                                                <span class="icon-feather-clock m-l-5"
                                                      title="<?php esc_html_e('Cron Setup Required', 'bookme') ?>"
                                                      data-tippy-placement="top"></span>
                                            <?php } ?>
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="notification_<?php echo $id ?>"
                                     aria-labelledby="notification_<?php echo $id ?>" data-parent="#accordion">
                                    <div class="card-body">
                                        <?php if (in_array($notification['type'], array('client_follow_up', 'client_reminder'))) { ?>
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <?php } ?>
                                                <div class="form-group">
                                                    <label for="notification_subject_<?php echo $id ?>"><?php esc_html_e('Subject', 'bookme') ?></label>
                                                    <input type="text" class="form-control"
                                                           id="notification_subject_<?php echo $id ?>"
                                                           name="notification[<?php echo $id ?>][subject]"
                                                           value="<?php echo esc_attr($notification['subject']) ?>"/>
                                                </div>
                                                <?php if (in_array($notification['type'], array('client_follow_up', 'client_reminder'))) { ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="notification_time_<?php echo $id ?>"><?php esc_html_e('Sending Time', 'bookme') ?></label>
                                                    <select class="form-control"
                                                            id="notification_time_<?php echo $id ?>"
                                                            name="<?php echo $notification['type'] ?>_cron_hour">
                                                        <?php foreach (range(0, 23, 0.5) as $time) { ?>
                                                            <option value="<?php echo $time; ?>"
                                                                <?php selected($cron_reminder[$notification['type']], $time) ?>>
                                                                <?php echo \Bookme\Inc\Mains\Functions\DateTime::build_time_string($time * HOUR_IN_SECONDS, false) ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                        <div class="form-group">
                                            <label><?php esc_html_e('Message', 'bookme') ?></label>
                                            <?php
                                            $settings = array(
                                                'textarea_name' => 'notification[' . $id . '][message]',
                                                'media_buttons' => false,
                                                'editor_height' => 300,
                                                'tinymce' => array(
                                                    'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,' .
                                                        'bullist,blockquote,|,justifyleft,justifycenter' .
                                                        ',justifyright,justifyfull,|,link,unlink,|' .
                                                        ',spellchecker,wp_fullscreen,wp_adv'
                                                )
                                            );
                                            wp_editor($notification['message'], 'notification_message_' . $id, $settings);
                                            ?>
                                        </div>
                                        <?php switch ($notification['type']) {
                                            case 'client_new_wp_user':
                                                include 'shortcodes-wp-user.php';
                                                break;
                                            default:
                                                include 'shortcodes.php';
                                        } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    if (!empty($notifications['combined'])) { ?>
                        <div class="m-b-20"><strong><?php esc_html_e('Combined Notifications', 'bookme') ?></strong>
                        </div>
                        <?php
                        foreach ($notifications['combined'] as $notification) {
                            if ($notification['to_customer']) {
                                $id = $notification['id'];
                                $name = Notification::get_name($notification['type']);
                                $test_notifications[] = array('id' => $id, 'name' => esc_html__('Customer', 'bookme').' '.$name);
                                ?>
                                <div class="card bookme-card">
                                    <div class="card-header d-flex align-items-center">
                                        <input name="notification[<?php echo $id ?>][active]" value="0"
                                               type="hidden">
                                        <label class="switch switch-sm m-r-10">
                                            <input name="notification[<?php echo $id ?>][active]" type="checkbox"
                                                   id="active_<?php echo $id ?>"
                                                   value="1" <?php checked($notification['active']) ?>>
                                            <span class="switch-state"></span>
                                        </label>
                                        <h5 class="mb-0">
                                            <button class="btn btn-link pl-0" data-toggle="collapse"
                                                    data-target="#notification_<?php echo $id ?>"
                                                    aria-expanded="false" aria-controls="notification_<?php echo $id ?>"
                                                    type="button">
                                                <?php echo esc_html($name) ?>
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="collapse" id="notification_<?php echo $id ?>"
                                         aria-labelledby="notification_<?php echo $id ?>" data-parent="#accordion">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="notification_subject_<?php echo $id ?>"><?php esc_html_e('Subject', 'bookme') ?></label>
                                                <input type="text" class="form-control"
                                                       id="notification_subject_<?php echo $id ?>"
                                                       name="notification[<?php echo $id ?>][subject]"
                                                       value="<?php echo esc_attr($notification['subject']) ?>"/>
                                            </div>
                                            <div class="form-group">
                                                <label><?php esc_html_e('Message', 'bookme') ?></label>
                                                <?php
                                                $settings = array(
                                                    'textarea_name' => 'notification[' . $id . '][message]',
                                                    'media_buttons' => false,
                                                    'editor_height' => 300,
                                                    'tinymce' => array(
                                                        'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,' .
                                                            'bullist,blockquote,|,justifyleft,justifycenter' .
                                                            ',justifyright,justifyfull,|,link,unlink,|' .
                                                            ',spellchecker,wp_fullscreen,wp_adv'
                                                    )
                                                );
                                                wp_editor($notification['message'], 'notification_message_' . $id, $settings);
                                                ?>
                                            </div>
                                            <?php include 'shortcodes-combined.php'; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    <?php } ?>
                </div>
                <div class="form-divider"></div>
                <div>
                    <h5 class="m-b-20"><?php esc_html_e('To Staff Members', 'bookme') ?></h5>
                    <?php foreach ($notifications['single'] as $notification) {
                        if ($notification['to_staff']) {
                            $id = $notification['id'];
                            $name = Notification::get_name($notification['type']);
                            $test_notifications[] = array('id' => $id, 'name' => esc_html__('Staff Member', 'bookme').' '.$name);
                            ?>
                            <div class="card bookme-card">
                                <div class="card-header d-flex align-items-center">
                                    <input name="notification[<?php echo $id ?>][active]" value="0"
                                           type="hidden">
                                    <label class="switch switch-sm m-r-10">
                                        <input name="notification[<?php echo $id ?>][active]" type="checkbox"
                                               id="active_<?php echo $id ?>"
                                               value="1" <?php checked($notification['active']) ?>>
                                        <span class="switch-state"></span>
                                    </label>
                                    <h5 class="mb-0">
                                        <button class="btn btn-link pl-0" data-toggle="collapse"
                                                data-target="#notification_<?php echo $id ?>"
                                                aria-expanded="false" aria-controls="notification_<?php echo $id ?>"
                                                type="button">
                                            <?php echo esc_html($name);
                                            if ($notification['type'] == 'staff_agenda') { ?>
                                                <span class="icon-feather-clock m-l-5"
                                                      title="<?php esc_html_e('Cron Setup Required', 'bookme') ?>"
                                                      data-tippy-placement="top"></span>
                                            <?php } ?>
                                        </button>
                                    </h5>
                                </div>
                                <div class="collapse" id="notification_<?php echo $id ?>"
                                     aria-labelledby="notification_<?php echo $id ?>" data-parent="#accordion">
                                    <div class="card-body">
                                        <?php if ($notification['type'] == 'staff_agenda'){ ?>
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <?php } ?>
                                                <div class="form-group">
                                                    <label for="notification_subject_<?php echo $id ?>"><?php esc_html_e('Subject', 'bookme') ?></label>
                                                    <input type="text" class="form-control"
                                                           id="notification_subject_<?php echo $id ?>"
                                                           name="notification[<?php echo $id ?>][subject]"
                                                           value="<?php echo esc_attr($notification['subject']) ?>"/>
                                                </div>
                                                <?php if ($notification['type'] == 'staff_agenda'){ ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="notification_time_<?php echo $id ?>"><?php esc_html_e('Sending Time', 'bookme') ?></label>
                                                    <select class="form-control"
                                                            id="notification_time_<?php echo $id ?>"
                                                            name="<?php echo $notification['type'] ?>_cron_hour">
                                                        <?php foreach (range(0, 23, 0.5) as $time) { ?>
                                                            <option value="<?php echo $time; ?>"
                                                                <?php selected($cron_reminder[$notification['type']], $time) ?>>
                                                                <?php echo \Bookme\Inc\Mains\Functions\DateTime::build_time_string($time * HOUR_IN_SECONDS, false) ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                        <div class="form-group">
                                            <label><?php esc_html_e('Message', 'bookme') ?></label>
                                            <?php
                                            $settings = array(
                                                'textarea_name' => 'notification[' . $id . '][message]',
                                                'media_buttons' => false,
                                                'editor_height' => 300,
                                                'tinymce' => array(
                                                    'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,' .
                                                        'bullist,blockquote,|,justifyleft,justifycenter' .
                                                        ',justifyright,justifyfull,|,link,unlink,|' .
                                                        ',spellchecker,wp_fullscreen,wp_adv'
                                                )
                                            );
                                            wp_editor($notification['message'], 'notification_message_' . $id, $settings);
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <input name="notification[<?php echo $id ?>][to_admin]" type="hidden"
                                                   value="0">
                                            <label class="switch switch-sm m-r-10">
                                                <input name="notification[<?php echo $id ?>][to_admin]" type="checkbox"
                                                       id="notification_<?php echo $id ?>_to_admin"
                                                       value="1" <?php checked($notification['to_admin']) ?>>
                                                <span class="switch-state"></span>
                                            </label>
                                            <label class="m-b-0"
                                                   for="notification_<?php echo $id ?>_to_admin"><?php esc_html_e('Send a copy to admin', 'bookme') ?></label>
                                        </div>
                                        <?php switch ($notification['type']) {
                                            case 'staff_agenda':
                                                include 'shortcodes-staff-agenda.php';
                                                break;
                                            default:
                                                include 'shortcodes.php';
                                        } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } ?>
                </div>
                <div class="form-divider"></div>
            </div>
            <div class="alert alert-info mb-0">
                <strong style="font-size: 16px"><?php esc_html_e('To send scheduled notifications please add the following line in your cron:', 'bookme'); ?></strong>
                <p><?php echo '*/15 * * * * /usr/local/bin/php ' . esc_url($cron_url); ?></p>
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
            <div class="float-right">
                <button type="button" class="btn btn-default" id="bm-test-email"><?php esc_html_e('Send Test Emails', 'bookme') ?></button>
            </div>
        </div>
    </div>
</form>
<?php include 'test-notification-panel.php' ?>