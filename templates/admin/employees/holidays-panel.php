<?php defined('ABSPATH') or die('No script kiddies please!');// No direct access ?>
<div class="bookme-page-wrapper">
    <header class="slidePanel-header">
        <div class="slidePanel-overlay-panel">
            <div class="slidePanel-heading">
                <h2><?php esc_html_e('Days Off', 'bookme') ?></h2>
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
        <div class="bookme-dayoff-nav">
            <div class="input-group input-group-lg">
                <div class="input-group-prepend">
                    <button class="btn btn-default" type="button" title="<?php esc_attr_e('Previous Year', 'bookme') ?>" id="bookme_dayoff_nav_left">
                        <i class="icon-feather-chevron-left"></i>
                    </button>
                </div>
                <input class="form-control text-center jcal_year" id="bookme_dayoff_nav_year"
                       readonly type="text" value="">
                <div class="input-group-append">
                    <button class="btn btn-default" type="button" title="<?php esc_attr_e('Next Year', 'bookme') ?>" id="bookme_dayoff_nav_right">
                        <i class="icon-feather-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="bookme-cal-wrap" data-holidays="<?php echo esc_attr(json_encode($holidays)) ?>" data-id="<?php echo $id ?>"></div>
    </div>
</div>