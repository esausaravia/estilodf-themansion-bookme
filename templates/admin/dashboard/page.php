<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('dashboard') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php _e('Dashboard', 'bookme') ?></h2>
                                <h6 class="mb-0"><?php _e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="bookme-card card">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-sm-6 col-md-4">
                                            <div class="position-relative">
                                                <button type="button" class="btn btn-default w-100" id="bm-filter-date"
                                                        data-date="<?php echo date('Y-m-d', strtotime('first day of')) ?> - <?php echo date('Y-m-d', strtotime('last day of')) ?>"
                                                        title="<?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date('first day of this month') ?> - <?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date('last day of this month') ?>">
                                                    <i class="icon-feather-calendar"></i>
                                                    <span>
                                <?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date('first day of this month') ?> - <?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date('last day of this month') ?>
                            </span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-8 text-right">
                                            <?php esc_html_e('Display data for selected time period.','bookme') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="fun-fact">
                                                <div class="fun-fact-icon"
                                                     style="background-color: rgba(54, 189, 120, 0.07);">
                                                    <i class="icon-feather-check-square"
                                                       style="color: rgb(54, 189, 120);"></i>
                                                </div>
                                                <div class="fun-fact-text">
                                                    <span><?php esc_html_e('Approved Bookings', 'bookme') ?></span>
                                                    <h4 id="bm-approved-booking"></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="fun-fact">
                                                <div class="fun-fact-icon"
                                                     style="background-color: rgba(239, 168, 15, 0.07);">
                                                    <i class="icon-feather-clock" style="color: rgb(239, 168, 15);"></i>
                                                </div>
                                                <div class="fun-fact-text">
                                                    <span><?php esc_html_e('Pending Bookings', 'bookme') ?></span>
                                                    <h4 id="bm-pending-booking"></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="fun-fact">
                                                <div class="fun-fact-icon"
                                                     style="background-color: rgba(81, 54, 189, 0.07);">
                                                    <i class="icon-feather-hash" style="color: rgb(54 96 189);"></i>
                                                </div>
                                                <div class="fun-fact-text">
                                                    <span><?php esc_html_e('Total Bookings', 'bookme') ?></span>
                                                    <h4 id="bm-total-booking"></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="fun-fact">
                                                <div class="fun-fact-icon">
                                                    <i class="icon-feather-activity"></i>
                                                </div>
                                                <div class="fun-fact-text">
                                                    <span><?php esc_html_e('Revenue', 'bookme') ?></span>
                                                    <h4 id="bm-revenue"></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="chart bm-chart">
                                        <canvas id="bm-chart" width="100" height="45"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid Ends-->
            </div>
            <?php Fragments::render_footer() ?>
        </div>
    </div>
</div>