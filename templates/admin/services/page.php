<?php defined('ABSPATH') or die('No script kiddies please!');// No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('services') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php esc_html_e('Services', 'bookme') ?></h2>
                                <h6 class="mb-0"><?php esc_html_e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid services-page">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="bookme-card card category-box">
                                <div class="card-body bm-categories">
                                    <h5><?php esc_html_e('Categories', 'bookme'); ?></h5>
                                    <div class="category-item category-item-all active">
                                        <?php esc_html_e('All Services', 'bookme'); ?>
                                    </div>
                                    <ul class="nav bm-categories-list">
                                        <?php foreach ($categories as $category) {
                                            include 'category-item.php';
                                        } ?>
                                    </ul>
                                    <button type="button"
                                            class="btn btn-primary w-100 bm-ripple-effect bm-add-category">
                                        <i class="icon-feather-plus"></i> <?php esc_html_e('Add Category', 'bookme') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-12">
                            <div class="bookme-card card all-services-box">
                                <div class="card-header">
                                    <h5 class="category-item-title"><?php esc_html_e('All Services', 'bookme'); ?></h5>

                                    <div class="card-header-right">
                                        <button type="button" data-url="<?php echo $service_panel_url ?>"
                                                data-toggle="slidePanel" class="btn btn-primary ripple-effect">
                                            <i class="icon-feather-plus"></i> <?php esc_html_e('Add Service', 'bookme') ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="bm-no-result" <?php if (!empty ($services)) { ?>style="display: none;"<?php } ?>>
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#e2e6ec" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                        </div>
                                        <p><?php esc_html_e('No Services Available Here.', 'bookme'); ?></p>
                                    </div>
                                    <div class="table-responsive services-wrapper">
                                        <?php include 'services-list.php'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid Ends-->
            </div>
            <?php Fragments::render_footer(); ?>
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
        <button type="button" class="front-icon btn btn-primary btn-floating" data-url="<?php echo $service_panel_url; ?>" data-toggle="slidePanel">
            <i class="icon-feather-plus animation-scale-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="back-icon btn btn-primary btn-floating">
            <i class="icon-feather-x animation-scale-up" aria-hidden="true"></i>
        </button>
    </div>

    <div class="modal fade" id="update-service-dialog" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="icon-feather-alert-circle"></i> <?php esc_html_e("Update Service",'bookme') ?></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <p><?php esc_html_e("You are changing a setting which is also set for each staff member separately. Do you want to update it for all staff members?",'bookme') ?></p>
                    <div class="checkbox">
                        <input type="checkbox" id="remeber_choice" value="1">
                        <label for="remeber_choice"><span class="checkbox-icon"></span> <?php esc_html_e('Remember my choice', 'bookme') ?></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default bm-no" type="button"><?php esc_html_e('No', 'bookme') ?></button>
                    <button class="btn btn-primary bm-yes" type="button"><?php esc_html_e('Yes', 'bookme') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>