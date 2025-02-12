<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments;
use Bookme\Inc\Mains\Tables\CustomerBooking; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('all-bookings') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php esc_html_e('Bookings', 'bookme') ?></h2>
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
                                <button type="button" class="btn btn-default ripple-effect bm-export-booking">
                                    <i class="icon-feather-share"></i> <?php esc_html_e('Export CSV', 'bookme') ?>
                                </button>
                                <button type="button" class="btn btn-primary ripple-effect bm-add-booking">
                                    <i class="icon-feather-plus"></i> <?php esc_html_e('Add Booking', 'bookme') ?>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row row-sm theme-form">
                                <div class="col-md-4 col-lg-1">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="bm-filter-id"
                                               placeholder="<?php esc_attr_e('No.', 'bookme') ?>"/>
                                    </div>
                                </div>
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
                                        <select class="form-control bm-select2" id="bm-filter-customer"
                                                data-placeholder="<?php esc_attr_e('Customer', 'bookme') ?>">
                                            <?php foreach ($customers as $customer) { ?>
                                                <option value="<?php echo $customer['id'] ?>"><?php esc_html_e($customer['full_name']) ?></option>
                                            <?php } ?>
                                        </select>
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
                                        <select class="form-control bm-select2" id="bm-filter-status"
                                                data-placeholder="<?php esc_attr_e('Status', 'bookme') ?>">
                                            <option value="<?php echo CustomerBooking::STATUS_PENDING ?>"><?php echo CustomerBooking::status_to_string(CustomerBooking::STATUS_PENDING) ?></option>
                                            <option value="<?php echo CustomerBooking::STATUS_APPROVED ?>"><?php echo CustomerBooking::status_to_string(CustomerBooking::STATUS_APPROVED) ?></option>
                                            <option value="<?php echo CustomerBooking::STATUS_CANCELLED ?>"><?php echo CustomerBooking::status_to_string(CustomerBooking::STATUS_CANCELLED) ?></option>
                                            <option value="<?php echo CustomerBooking::STATUS_REJECTED ?>"><?php echo CustomerBooking::status_to_string(CustomerBooking::STATUS_REJECTED) ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="dataTables_wrapper">
                                <table class="table table-striped" id="bm-bookings-table">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e('No.', 'bookme') ?></th>
                                        <th><?php esc_html_e('Booking Date', 'bookme') ?></th>
                                        <th><?php esc_html_e('Customer', 'bookme') ?></th>
                                        <th><?php echo \Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_employee') ?></th>
                                        <th><?php echo \Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_service') ?></th>
                                        <th><?php esc_html_e('Duration', 'bookme') ?></th>
                                        <th><?php esc_html_e('Status', 'bookme') ?></th>
                                        <th><?php esc_html_e('Payment', 'bookme') ?></th>
                                        <th><?php esc_html_e('Online Meeting', 'bookme') ?></th>
                                        <?php foreach ($custom_fields as $custom_field) { ?>
                                            <th class="none"><?php echo $custom_field->label ?></th>
                                        <?php } ?>
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
            <button type="button" data-toggle="modal" data-target="#bm-booking-delete-dialog"
                    class="btn btn-danger btn-floating animation-slide-bottom">
                <i class="icon icon-feather-trash-2" aria-hidden="true"></i>
            </button>
        </div>
        <button type="button" class="front-icon btn btn-primary btn-floating bm-add-booking">
            <i class="icon-feather-plus animation-scale-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="back-icon btn btn-primary btn-floating">
            <i class="icon-feather-x animation-scale-up" aria-hidden="true"></i>
        </button>
    </div>
    <?php Fragments::render_booking_panel();
    Fragments::render_booking_delete_dialog();
    Fragments::render_payment_dialog();?>
</div>