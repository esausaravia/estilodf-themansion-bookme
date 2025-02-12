<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access
use Bookme\Inc\Mains\Functions\System;
use Bookme\App\Admin;

?>
<!-- Page Sidebar Start-->
<div class="iconsidebar-menu iconbar-mainmenu-close">
    <div class="sidebar">
        <ul class="iconMenu-bar custom-scrollbar">
            <?php if (System::is_current_user_admin()) { ?>
                <li data-page="dashboard">
                    <a class="bar-icons" href="<?php echo System::esc_admin_url(Admin\Dashboard::page_slug); ?>">
                        <i class="icon-feather-home"></i><span><?php esc_html_e('Dashboard', 'bookme') ?></span>
                    </a>
                </li>
            <?php }
            if(System::is_current_user_admin() || (get_option('bookme_allow_staff_edit_profile') == 1)) {
            ?>
            <li data-page="staff-members">
                <a class="bar-icons" href="<?php echo System::esc_admin_url(Admin\Employees::page_slug); ?>">
                    <?php if (System::is_current_user_admin()) { ?>
                    <i class="icon-feather-briefcase"></i><span><?php esc_html_e('Staff Members', 'bookme') ?></span>
                    <?php } else { ?>
                        <i class="icon-feather-user"></i><span><?php esc_html_e('Profile', 'bookme') ?></span>
                    <?php } ?>
                </a>
            </li>
            <?php
            }
            if (System::is_current_user_admin()) { ?>
                <li data-page="services">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\Services::page_slug); ?>">
                        <i class="icon-feather-gift"></i><span><?php esc_html_e('Services', 'bookme') ?></span>
                    </a>
                </li>
                <li data-page="all-bookings">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\Bookings::page_slug); ?>">
                        <i class="icon-feather-file-text"></i><span><?php esc_html_e('All Bookings', 'bookme') ?></span>
                    </a>
                </li>
            <?php } ?>
            <li data-page="calendar">
                <a class="bar-icons"
                   href="<?php echo System::esc_admin_url(Admin\Calendar::page_slug); ?>">
                    <i class="icon-feather-calendar"></i><span><?php esc_html_e('Calendar', 'bookme') ?></span>
                </a>
            </li>
            <?php if (System::is_current_user_admin()) { ?>
                <li data-page="customers">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\Customers::page_slug); ?>">
                        <i class="icon-feather-users"></i><span><?php esc_html_e('Customers', 'bookme') ?></span>
                    </a>
                </li>
                <li data-page="payments">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\Payments::page_slug); ?>">
                        <i class="icon-feather-award"></i><span><?php esc_html_e('Payments', 'bookme') ?></span>
                    </a>
                </li>
                <li data-page="appearance">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\Appearance::page_slug); ?>">
                        <i class="icon-feather-edit"></i><span><?php esc_html_e('Appearance', 'bookme') ?></span>
                    </a>
                </li>
                <li data-page="notifications">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\Notifications::page_slug); ?>">
                        <i class="icon-feather-bell"></i><span><?php esc_html_e('Notifications', 'bookme') ?></span>
                    </a>
                    <ul class="iconbar-mainmenu custom-scrollbar nav">
                        <li class="iconbar-header"><?php esc_html_e('Notifications', 'bookme') ?></li>
                        <li class="active">
                            <a href="#bookme_notifications_email" data-toggle="tab"
                               class="active"><?php esc_html_e('Email Notifications', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_notifications_sms"
                               data-toggle="tab"><?php esc_html_e('SMS Notifications', 'bookme') ?></a>
                        </li>
                    </ul>
                </li>
                <li data-page="custom-fields">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\CustomFields::page_slug); ?>">
                        <i
                                class="icon-feather-grid"></i><span><?php esc_html_e('Custom Fields', 'bookme') ?></span>
                    </a>
                </li>
                <li data-page="coupons">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\Coupons::page_slug); ?>">
                        <i class="icon-feather-copy"></i><span><?php esc_html_e('Coupons', 'bookme') ?></span>
                    </a>
                </li>
                <li data-page="settings">
                    <a class="bar-icons"
                       href="<?php echo System::esc_admin_url(Admin\Settings::page_slug); ?>">
                        <i class="icon-feather-settings"></i><span><?php esc_html_e('Settings', 'bookme') ?></span>
                    </a>
                    <ul class="iconbar-mainmenu custom-scrollbar nav">
                        <li class="iconbar-header"><?php esc_html_e('Settings', 'bookme') ?></li>
                        <li class="active">
                            <a href="#bookme_settings_general" data-toggle="tab"
                               class="active"><?php esc_html_e('General', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_customers"
                               data-toggle="tab"><?php esc_html_e('Customers', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_cart"
                               data-toggle="tab"><?php esc_html_e('Cart', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_company"
                               data-toggle="tab"><?php esc_html_e('Company', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_google_calendar"
                               data-toggle="tab"><?php esc_html_e('Google Calendar', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_google_recaptcha"
                               data-toggle="tab"><?php esc_html_e('Google reCAPTCHA', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_online_meeting"
                               data-toggle="tab"><?php esc_html_e('Zoom', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_woo_commerce"
                               data-toggle="tab"><?php esc_html_e('WooCommerce', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_payments"
                               data-toggle="tab"><?php esc_html_e('Payments', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_notifications"
                               data-toggle="tab"><?php esc_html_e('Notifications', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_working_hours"
                               data-toggle="tab"><?php esc_html_e('Working Hours', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_holidays"
                               data-toggle="tab"><?php esc_html_e('Holidays', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_labels"
                               data-toggle="tab"><?php esc_html_e('Labels', 'bookme') ?></a>
                        </li>
                        <li>
                            <a href="#bookme_settings_purchase_code"
                               data-toggle="tab"><?php esc_html_e('Purchase Code', 'bookme') ?></a>
                        </li>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
<!-- Page Sidebar Ends-->