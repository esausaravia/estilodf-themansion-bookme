<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('coupons') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php esc_html_e('Coupons', 'bookme') ?></h2>
                                <h6 class="mb-0"><?php esc_html_e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="bookme-card card">
                        <div class="card-header">
                            <h5>&nbsp;</h5>
                        <div class="card-header-right">
                            <button type="button" class="btn btn-primary ripple-effect bm-add-coupon">
                                <i class="icon-feather-plus"></i> <?php esc_html_e('Add Coupon', 'bookme') ?>
                            </button>
                        </div>
                        </div>
                        <div class="card-body">
                            <div class="dataTables_wrapper">
                                <table class="table table-striped" id="bm-coupon-table">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e('Code', 'bookme') ?></th>
                                        <th><?php esc_html_e('Discount (%)', 'bookme') ?></th>
                                        <th><?php esc_html_e('Deduction', 'bookme') ?></th>
                                        <th><?php esc_html_e('Services', 'bookme') ?></th>
                                        <th><?php esc_html_e('Usage limit', 'bookme') ?></th>
                                        <th><?php esc_html_e('Number of times used', 'bookme') ?></th>
                                        <th width="20"></th>
                                        <th width="20">
                                            <div class="checkbox">
                                                <input type="checkbox" id="bm-checkbox-all">
                                                <label for="bm-checkbox-all"><span class="checkbox-icon"></span></label>
                                            </div>
                                        </th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid Ends-->
            </div>
            <?php Fragments::render_footer() ?>
        </div>
    </div>

    <!-- Site Action -->
    <div class="site-action">
        <div class="site-action-buttons">
            <button type="button" id="bm-delete-button"
                    class="btn btn-danger btn-floating animation-slide-bottom">
                <i class="icon icon-feather-trash-2" aria-hidden="true"></i>
            </button>
        </div>
        <button type="button" class="front-icon btn btn-primary btn-floating bm-add-coupon">
            <i class="icon-feather-plus animation-scale-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="back-icon btn btn-primary btn-floating">
            <i class="icon-feather-x animation-scale-up" aria-hidden="true"></i>
        </button>
    </div>
    <?php include 'coupon-panel.php'; ?>
</div>