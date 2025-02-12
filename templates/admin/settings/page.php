<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('settings') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php esc_html_e('Settings', 'bookme') ?></h2>
                                <h6 class="mb-0"><?php esc_html_e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="tab-content">
                        <div class="tab-pane active" id="bookme_settings_general">
                            <?php include 'general-tab.php'; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_customers">
                            <?php include 'customer-tab.php'; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_cart">
                            <?php include 'cart-tab.php'; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_company">
                            <?php include "company-tab.php"; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_google_calendar">
                            <?php include "google-calendar-tab.php"; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_google_recaptcha">
                            <?php include "google-recaptcha-tab.php"; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_online_meeting">
                            <?php include "online_meeting-tab.php"; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_woo_commerce">
                            <?php include 'woocommerce-tab.php'; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_payments">
                            <?php include "payments-tab.php"; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_notifications">
                            <?php include "notification-tab.php"; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_working_hours">
                            <?php include "working-hours-tab.php"; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_holidays">
                            <?php include 'holidays-tab.php'; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_labels">
                            <?php include 'labels-tab.php'; ?>
                        </div>
                        <div class="tab-pane" id="bookme_settings_purchase_code">
                            <?php include 'purchase-code-tab.php'; ?>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid Ends-->
            </div>
            <?php Fragments::render_footer() ?>
        </div>
    </div>
</div>