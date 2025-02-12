<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('calendar') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php esc_html_e('Calendar', 'bookme') ?></h2>
                                <h6 class="mb-0"><?php esc_html_e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="bookme-card card">
                        <div class="card-header p-l-20">
                            <div class="bookme-calendar-employees">
                                <div class="bookme-calendar-employee active" data-id="0">
                                    <div class="bm-profile-photo">
                                        <div class="bm-all"><?php esc_html_e('All','bookme') ?></div>
                                    </div>
                                    <p><?php esc_html_e('All','bookme') ?></p>
                                </div>
                                <?php foreach ($employees as $employee) { ?>
                                    <div class="bookme-calendar-employee" data-id="<?php echo $employee['id'] ?>">
                                        <div class="bm-profile-photo">
                                            <?php
                                            $img = wp_get_attachment_image_src($employee['attachment_id'], 'thumbnail');
                                            $img_url = $img ? $img[0] : BOOKME_URL . '/assets/admin/images/user-default.png';
                                            ?>
                                            <img src="<?php echo esc_url($img_url); ?>"
                                                 alt="<?php echo esc_attr($employee['full_name']); ?>">
                                        </div>
                                        <p class="bookme-calendar-employee-name" title="<?php echo esc_attr($employee['full_name']) ?>"><?php echo esc_html($employee['full_name']) ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="bookme-calendar">
                                <div class="bm-calendar-scroll">
                                    <div class="bm-full-calendar"></div>
                                    <div class="bm-calendar-loading" style="display: none;">
                                        <div class="cssload-speeding-wheel"></div>
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

    <!-- Site Action -->
    <div class="site-action">
        <div class="site-action-buttons">
            <button type="button" id="bm-delete-button"
                    class="btn btn-danger btn-floating animation-slide-bottom">
                <i class="icon icon-feather-trash-2" aria-hidden="true"></i>
            </button>
        </div>
        <button type="button" class="front-icon btn btn-primary btn-floating bm-new-booking">
            <i class="icon-feather-plus animation-scale-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="back-icon btn btn-primary btn-floating">
            <i class="icon-feather-x animation-scale-up" aria-hidden="true"></i>
        </button>
    </div>
    <?php Fragments::render_booking_panel();
        Fragments::render_booking_delete_dialog(); ?>
</div>