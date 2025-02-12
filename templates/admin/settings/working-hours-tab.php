<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="working_hours">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Working Hours', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <p><?php esc_html_e('These details will be used as a placeholder for newly added staff members.', 'bookme') ?></p>
            <div id="schedule">
                <?php
                $start_of_week = (int)get_option('start_of_week');
                for ($i = 0; $i < 7; $i++) {
                    $day = strtolower(\Bookme\Inc\Mains\Functions\DateTime::get_week_day_by_number(($i + $start_of_week) % 7));

                $start_option = 'bookme_wh_' . $day . '_start';
                    $end_option = 'bookme_wh_' . $day . '_end';
                    $start = get_option($start_option);
                    $end = get_option($end_option);
                    ?>
                    <div class="form-group">
                        <div class="p-2 bg-light m-b-10"><strong><?php esc_html_e(ucfirst($day)) ?></strong></div>
                        <div class="row align-items-center">
                            <div class="col-sm-1 m-b-5">
                                <label class="switch" data-tippy-placement="top"
                                       title="<?php esc_attr_e('Day Off', 'bookme') ?>">
                                    <input class="schedule-day-off"
                                           type="checkbox" <?php checked(!$start) ?>><span
                                            class="switch-state"></span>
                                </label>
                            </div>
                            <div class="col-sm-4 m-b-5 schedule-day-off-hide">
                                <div class="input-group">
                                    <select name="<?php echo $start_option; ?>"
                                            class="form-control schedule-start"
                                            data-last-value="<?php echo $start; ?>">
                                        <?php echo \Bookme\Inc\Mains\Functions\System::schedule_options($start, 'from'); ?>
                                    </select>
                                    <span class="input-group-text">-</span>
                                    <select name="<?php echo $end_option; ?>"
                                            class="form-control schedule-end"
                                            data-last-value="<?php echo $end; ?>">
                                        <?php echo \Bookme\Inc\Mains\Functions\System::schedule_options($end); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>