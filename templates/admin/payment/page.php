<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('payments') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php esc_html_e('Payments', 'bookme') ?></h2>
                                <h6 class="mb-0"><?php esc_html_e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="bookme-card card">
                        <div class="card-body">
                            <div class="row row-sm theme-form">
                                <div class="col-md-4 col-lg-3">
                                    <div class="m-b-20 position-relative">
                                        <button type="button" class="btn btn-block btn-default" id="bm-filter-date"
                                                data-date="<?php echo date('Y-m-d', strtotime('first day of')) ?> - <?php echo date('Y-m-d', strtotime('last day of')) ?>"
                                                title="<?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date('first day of this month') ?> - <?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date('last day of this month') ?>">
                                            <i class="icon-feather-calendar"></i>
                                            <span>
                                <?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date('first day of this month') ?> - <?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date('last day of this month') ?>
                            </span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <select class="form-control bm-select2" id="bm-filter-employee"
                                                data-placeholder="<?php echo esc_attr(\Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_employee')) ?>">
                                            <?php foreach ($employees as $staff) { ?>
                                                <option value="<?php echo $staff['id'] ?>"><?php esc_html_e($staff['full_name']) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <select class="form-control bm-select2" id="bm-filter-service"
                                                data-placeholder="<?php echo esc_attr(\Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_service')) ?>">
                                            <?php foreach ($services as $service) { ?>
                                                <option value="<?php echo $service['id'] ?>"><?php esc_html_e($service['title']) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <select class="form-control bm-select2" id="bm-filter-type"
                                                data-placeholder="<?php esc_attr_e( 'Type', 'bookme' ) ?>">
                                            <?php foreach ( $types as $type ) { ?>
                                                <option value="<?php echo esc_attr( $type ) ?>">
                                                    <?php echo \Bookme\Inc\Mains\Tables\Payment::type_to_string( $type ) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="dataTables_wrapper">
                                <table class="table table-striped" id="bm-payment-table">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Date', 'bookme' ) ?></th>
                                        <th><?php esc_html_e( 'Customer', 'bookme' ) ?></th>
                                        <th><?php echo esc_attr(\Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_employee')) ?></th>
                                        <th><?php echo esc_attr(\Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_service')) ?></th>
                                        <th><?php esc_html_e( 'Booking Date', 'bookme' ) ?></th>
                                        <th><?php esc_html_e( 'Amount', 'bookme' ) ?></th>
                                        <th><?php esc_html_e( 'Type', 'bookme' ) ?></th>
                                        <th><?php esc_html_e( 'Status', 'bookme' ) ?></th>
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
        <button type="button" class="back-icon btn btn-primary btn-floating">
            <i class="icon-feather-x animation-scale-up" aria-hidden="true"></i>
        </button>
    </div>

    <?php Fragments::render_payment_dialog(); ?>
</div>