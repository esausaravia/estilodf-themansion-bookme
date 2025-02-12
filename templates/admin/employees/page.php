<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('staff-members') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                    <div class="alert alert-info">
                        <h5 class="m-t-20"><?php esc_html_e('Thank you for purchasing Bookme plugin.', 'bookme') ?></h5>
                        <h6><?php esc_html_e('Follow the below steps, to start using the plugin.', 'bookme') ?></h6>
                        <ol>
                            <li><?php echo sprintf(__('Please verify your license by providing a valid purchase code. <a href="%s">Click here to verify.</a>', 'bookme'),  \Bookme\Inc\Mains\Functions\System::esc_admin_url(\Bookme\App\Admin\Settings::page_slug, array('tab' => 'purchase_code'))) ?></li>
                            <li><?php esc_html_e('Add staff members.', 'bookme') ?></li>
                            <li><?php esc_html_e('Add services and assign them to staff members.', 'bookme') ?></li>
                            <li><?php _e('Go to Posts/Pages and use <strong>[bookme]</strong> shortcode to publish the booking form on your website.', 'bookme') ?></li>
                            <li><?php esc_html_e('Use the plugin now.', 'bookme') ?></li>
                        </ol>
                    </div>
                    </div>
                </div>
            </div>
            <?php Fragments::render_footer() ?>
        </div>
    </div>
</div>