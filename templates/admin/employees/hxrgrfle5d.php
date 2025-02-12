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
                <?php if (empty($employees)) { ?>
                    <div class="container-fluid">
                        <div class="page-header">
                            <div class="alert alert-info">
                                <h5 class="m-t-20"><?php esc_html_e('Thank you for purchasing Bookme plugin.', 'bookme') ?></h5>
                                <h6><?php esc_html_e('Follow the below steps, to start using the plugin.', 'bookme') ?></h6>
                                <ol>
                                    <li><?php esc_html_e('Add staff members.', 'bookme') ?></li>
                                    <li><?php esc_html_e('Add services and assign them to staff members.', 'bookme') ?></li>
                                    <li><?php _e('Go to Posts/Pages and use <strong>[bookme]</strong> shortcode to publish the booking form on your website.', 'bookme') ?></li>
                                    <li><?php esc_html_e('Use the plugin now.', 'bookme') ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <?php if (\Bookme\Inc\Mains\Functions\System::is_current_user_admin()) { ?>
                                    <h2><?php esc_html_e('Staff Members', 'bookme') ?></h2>
                                <?php } else { ?>
                                    <h2><?php esc_html_e('Profile', 'bookme') ?></h2>
                                <?php } ?>
                                <h6 class="mb-0"><?php esc_html_e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="bookme-card card">
                                <?php if (\Bookme\Inc\Mains\Functions\System::is_current_user_admin()) { ?>
                                    <div class="card-header">
                                        <h5><?php esc_html_e('Total Members:', 'bookme'); ?> <span
                                                    class="text-primary bm-employee-count"><?php echo count($employees) ?></span>
                                        </h5>

                                        <div class="card-header-right">
                                            <button type="button" data-url="<?php echo $add_employee_panel_url ?>"
                                                    data-toggle="slidePanel" data-event="new_employee"
                                                    class="btn btn-primary ripple-effect">
                                                <i class="icon-feather-plus"></i> <?php esc_html_e('Add Member', 'bookme') ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="card-body">
                                    <div class="bm-no-result"
                                         <?php if (!empty ($employees)) { ?>style="display: none;"<?php } ?>>
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72"
                                                 viewBox="0 0 24 24" fill="none" stroke="#e2e6ec" stroke-width="1"
                                                 stroke-linecap="round" stroke-linejoin="round"
                                                 class="feather feather-grid">
                                                <rect x="3" y="3" width="7" height="7"></rect>
                                                <rect x="14" y="3" width="7" height="7"></rect>
                                                <rect x="14" y="14" width="7" height="7"></rect>
                                                <rect x="3" y="14" width="7" height="7"></rect>
                                            </svg>
                                        </div>
                                        <p><?php esc_html_e('No Members Available Here.', 'bookme'); ?></p>
                                    </div>
                                    <div class="table-responsive employees-wrapper"
                                         <?php if (empty ($employees)) { ?>style="display: none;"<?php } ?>>
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th class="cell-xm">
                                                    <div class="checkbox">
                                                        <input type="checkbox" id="bm-checkbox-all">
                                                        <label for="bm-checkbox-all"><span class="checkbox-icon"></span></label>
                                                    </div>
                                                </th>
                                                <th><?php esc_html_e('Name', 'bookme') ?></th>
                                                <th><?php esc_html_e('Services', 'bookme') ?></th>
                                                <th><?php esc_html_e('Contacts', 'bookme') ?></th>
                                                <th class="cell-sm"></th>
                                            </tr>
                                            </thead>
                                            <tbody id="employees-tbody">
                                            <?php
                                            foreach ($employees as $member) {
                                                include "employee-list.php";
                                            }
                                            ?>
                                            </tbody>
                                        </table>
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

    <?php if (\Bookme\Inc\Mains\Functions\System::is_current_user_admin()) { ?>
        <!-- Site Action -->
        <div class="site-action">
            <div class="site-action-buttons">
                <button type="button" id="bm-delete-button"
                        class="btn btn-danger btn-floating animation-slide-bottom">
                    <i class="icon icon-feather-trash-2" aria-hidden="true"></i>
                </button>
            </div>
            <button type="button" class="front-icon btn btn-primary btn-floating"
                    data-url="<?php echo $add_employee_panel_url; ?>" data-toggle="slidePanel"
                    data-event="new_employee">
                <i class="icon-feather-plus animation-scale-up" aria-hidden="true"></i>
            </button>
            <button type="button" class="back-icon btn btn-primary btn-floating">
                <i class="icon-feather-x animation-scale-up" aria-hidden="true"></i>
            </button>
        </div>
    <?php } ?>
</div>