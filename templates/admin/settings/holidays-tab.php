<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<div class="bookme-card card">
    <div class="card-header">
        <h5><?php esc_html_e('Holidays', 'bookme') ?></h5>
    </div>
    <div class="card-body" id="bookme-holidays">
        <div class="bookme-dayoff-nav">
            <div class="input-group input-group-lg">
                <div class="input-group-prepend">
                    <button class="btn btn-default" type="button" title="<?php esc_attr_e('Previous Year', 'bookme') ?>"
                            id="bookme_dayoff_nav_left">
                        <i class="icon-feather-chevron-left"></i>
                    </button>
                </div>
                <input class="form-control text-center jcal_year" id="bookme_dayoff_nav_year"
                       readonly type="text" value="">
                <div class="input-group-append">
                    <button class="btn btn-default" type="button" title="<?php esc_attr_e('Next Year', 'bookme') ?>"
                            id="bookme_dayoff_nav_right">
                        <i class="icon-feather-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="bookme-cal-wrap" data-holidays="<?php echo esc_attr(json_encode($holidays)) ?>"></div>
    </div>
    <div class="card-footer">
        <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
        <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
    </div>
</div>