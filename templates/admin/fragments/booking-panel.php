<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\Inc\Mains\Tables\CustomerBooking;
?>

<div id="bm-booking-panel" class="slidePanel <?php echo is_rtl() ? 'slidePanel-left' : 'slidePanel-right'; ?>">
    <div class="slidePanel-scrollable">
        <div>
            <div class="slidePanel-content">
                <header class="slidePanel-header">
                    <div class="slidePanel-overlay-panel">
                        <div class="slidePanel-heading">
                            <h2><?php esc_html_e('New Booking', 'bookme'); ?></h2>
                        </div>
                        <div class="slidePanel-actions">
                            <button class="btn-icon btn-primary ajax-save-booking" title="<?php esc_attr_e('Save', 'bookme') ?>">
                                <i class="icon-feather-check"></i>
                            </button>
                            <button class="btn-icon slidePanel-close" title="<?php esc_attr_e('Close', 'bookme') ?>">
                                <i class="icon-feather-x"></i>
                            </button>
                        </div>
                    </div>
                </header>
                <div class="slidePanel-inner">
                    <form class="theme-form" onsubmit="return false;">
                        <div id="bm-form-loader" style="display: none">
                            <div class="bookme-loading"></div>
                        </div>
                        <div id="bm-form-wrapper">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group form-required">
                                        <label for="bookme-staff"><?php esc_html_e('Staff Member', 'bookme') ?></label>
                                        <select id="bookme-staff" class="form-control">
                                            <?php foreach ($staff as $staff_member) { ?>
                                                <option value="<?php echo $staff_member['id'] ?>"><?php echo esc_html($staff_member['full_name']) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group form-required">
                                        <label for="bookme-service"><?php esc_html_e('Service', 'bookme') ?></label>
                                        <select id="bookme-service" class="form-control">
                                            <option value=""><?php esc_html_e('Select service', 'bookme') ?></option>
                                        </select>
                                        <span id="bm-error-service" class="bm-errors text-danger" style="display: none"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group form-required">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label for="bookme-date"><?php esc_html_e('Date', 'bookme') ?></label>
                                        <input id="bookme-date" class="form-control" type="text"
                                               autocomplete="off">
                                    </div>
                                    <div class="col-sm-6">
                                        <div id="bookme-time">
                                            <label for="bookme-start-time"><?php esc_html_e('Start time', 'bookme') ?></label>
                                            <select id="bookme-start-time" class="form-control">
                                                <?php foreach ($start_time as $time) { ?>
                                                    <option value="<?php echo $time['value'] ?>"><?php echo esc_html($time['title']) ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <span id="bm-error-time" class="bm-errors text-danger" style="display: none"></span>
                            </div>

                            <div class="form-group">
                                <label for="bookme-customer"><?php esc_html_e('Customers', 'bookme') ?> <span id="bm-customer-limit" title="<?php esc_attr_e('Selected / maximum', 'bookme') ?>" style="display: none">
                                (0/0)
                            </span></label>
                                <span id="bm-error-customer" class="bm-errors text-danger" style="display: none"></span>
                                <ul class="m-b-20" id="bm-customers-list"></ul>
                                <div id="bm-customer-selector">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="flex-grow-1">
                                                <select id="bookme-customer" multiple
                                                        data-placeholder="<?php esc_attr_e('Search customers', 'bookme') ?>"
                                                        class="form-control">
                                                    <?php foreach ($customers as $customer) { ?>
                                                        <option value="<?php echo $customer['id'] ?>"><?php echo esc_html($customer['name']) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary bm-new-customer">
                                                    <i class="icon-feather-plus"></i>
                                                    <?php esc_html_e('New customer', 'bookme') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group hidden" id="bm-online-meeting-wrapper">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label for="bookme-online-meeting-url"><?php esc_html_e('Online Meeting', 'bookme') ?></label>
                                        <div class="input-group">
                                            <input id="bookme-online-meeting-url" class="form-control" type="text"
                                                   readonly>
                                            <div class="input-group-append">
                                                <a href="#" class="btn btn-icon btn-default height" id="bookme-online-meeting-url-link" target="_blank" style="height: auto">
                                                    <i class="icon-feather-external-link"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="bookme-online-meeting-pass"><?php esc_html_e('Password', 'bookme') ?></label>
                                        <input id="bookme-online-meeting-pass" class="form-control" type="text"
                                               readonly>
                                    </div>
                                </div>
                            </div>

                            <div class=form-group>
                                <label for="bookme-notification">
                                    <?php esc_html_e('Notify Customers', 'bookme') ?>
                                    <i class="dashicons dashicons-editor-help"
                                       title="<?php esc_attr_e('Send email or SMS notifications to the customers and staff members.', 'bookme') ?>"
                                       data-tippy-placement="top"></i>
                                </label>
                                <select class="form-control" id="bookme-notification">
                                    <option value="no"><?php esc_html_e('Don\'t send', 'bookme') ?></option>
                                    <option value="changed_status"><?php esc_html_e('If status changed', 'bookme') ?></option>
                                    <option value="all"><?php esc_html_e('To all customers', 'bookme') ?></option>
                                </select>
                            </div>

                            <div class=form-group>
                                <label for="bookme-internal-note"><?php esc_html_e('Internal note', 'bookme') ?></label>
                                <textarea class="form-control" id="bookme-internal-note"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div payment-details-dialog="completePayment(payment_id, payment_title)"></div>
<?php
include "customer-details-panel.php";
\Bookme\App\Admin\Fragments::render_customer_panel();
?>
<script type="text/template" id="bm-customer-template">
    <li class="d-flex align-items-center m-b-10">
        <a class="flex-grow-1 bm-customer-name"
           title="<?php esc_attr_e('Edit booking details', 'bookme') ?>"
           href="javascript:void(0)"></a>
        <div class="d-flex align-items-center">
            <div class="dropdown m-l-5" style="line-height: 1">
                <button type="button"
                        class="btn-icon btn-sm m-l-5 bm-customer-status"
                        data-toggle="dropdown">
                    <i class="icon-feather-check approved"></i>
                </button>
                <ul class="dropdown-menu bm-status-selector">
                    <li class="approved" data-status="approved">
                        <i class="icon-feather-check approved"></i>
                        <?php echo esc_html(CustomerBooking::status_to_string(CustomerBooking::STATUS_APPROVED)) ?>
                    </li>
                    <li class="pending" data-status="pending">
                        <i class="icon-feather-clock pending"></i>
                        <?php echo esc_html(CustomerBooking::status_to_string(CustomerBooking::STATUS_PENDING)) ?>
                    </li>
                    <li class="cancelled" data-status="cancelled">
                        <i class="icon-feather-x cancelled"></i>
                        <?php echo esc_html(CustomerBooking::status_to_string(CustomerBooking::STATUS_CANCELLED)) ?>
                    </li>
                    <li class="rejected" data-status="rejected">
                        <i class="icon-feather-x-circle rejected"></i>
                        <?php echo esc_html(CustomerBooking::status_to_string(CustomerBooking::STATUS_REJECTED)) ?>
                    </li>
                </ul>
            </div>
            <button type="button" class="btn-icon btn-sm w-auto disabled m-l-5"
                    style="opacity:1;cursor:default;"><i
                        class="icon-feather-user position-static m-0"></i>&times;<span class="bm-customer-nop">1</span>
            </button>
            <a title="<?php esc_attr_e('Remove', 'bookme') ?>"
               class="icon-feather-trash-2 text-danger m-l-5 bm-remove-customer"
               href="javascript:void(0)"></a>
        </div>
    </li>
</script>