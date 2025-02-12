<?php defined('ABSPATH') or die('No script kiddies please!');// No direct access?>
<div class="m-t-5 schedule-break bm-break-<?php echo $break['id'] ?>" data-break-id="<?php echo $break['id'] ?>">
    <div class="btn-group btn-group-sm">
        <button type="button"
                class="btn btn-sm btn-primary bm-schedule-break"
                data-popover-content=".bookme-break-content-<?php echo $break['id'] ?>">
            <?php echo \Bookme\Inc\Mains\Functions\DateTime::format_interval( $break['start_time'], $break['end_time'] ) ?>
        </button>
        <button title="<?php _e('Delete break', 'bookme') ?>"
                type="button"
                class="btn btn-primary btn-sm delete-break">&times;
        </button>
    </div>
    <div class="bookme-break-content-<?php echo $break['id'] ?> hidden">
        <div class="input-group m-b-10 theme-form">
            <select class="form-control break-start"
                    data-id="<?php echo $break['staff_schedule_id'] ?>">
                <?php echo \Bookme\Inc\Mains\Functions\System::schedule_options($break['start_time'], 'break_from'); ?>
            </select>
            <span class="input-group-text">-</span>
            <select class="form-control break-end">
                <?php echo \Bookme\Inc\Mains\Functions\System::schedule_options($break['end_time']); ?>
            </select>
        </div>
        <div>
            <button class="btn-icon btn-primary ajax-save-break"
                    title="<?php esc_attr_e('Save', 'bookme') ?>" data-break-id="<?php echo $break['id'] ?>">
                <i class="icon-feather-check"></i>
            </button>
            <button class="btn-icon bm-popover-close"
                    title="<?php esc_attr_e('Close', 'bookme') ?>">
                <i class="icon-feather-x"></i>
            </button>
        </div>
    </div>
</div>