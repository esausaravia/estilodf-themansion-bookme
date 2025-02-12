<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('appearance') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php esc_html_e('Appearance', 'bookme') ?></h2>
                                <h6 class="mb-0"><?php esc_html_e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="alert alert-info">
                        <?php printf(__('To edit the labels of the booking form, <a href="%s" class="alert-link">click here</a>.', 'bookme'),
                            \Bookme\Inc\Mains\Functions\System::esc_admin_url(\Bookme\App\Admin\Settings::page_slug,array('tab'=>'labels'))
                        ); ?>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="bookme-card card theme-form">
                                <form method="post" id="bm-appearance-form">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center form-group">
                                            <div class="flex-grow-1">
                                                <strong><?php esc_html_e('Primary Color', 'bookme') ?></strong></div>
                                            <div>
                                                <div class="bm-primary-color-wrapper">
                                                    <button class="bm-color-picker"></button>
                                                    <input type="hidden" class="color-input" name="bookme_primary_color"
                                                           value="<?php echo get_option('bookme_primary_color'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center form-group">
                                            <div class="flex-grow-1">
                                                <strong><?php esc_html_e('Secondary Color', 'bookme') ?></label></strong>
                                            </div>
                                            <div>
                                                <div class="bm-secondary-color-wrapper">
                                                    <button class="bm-color-picker"></button>
                                                    <input type="hidden" class="color-input"
                                                           name="bookme_secondary_color"
                                                           value="<?php echo get_option('bookme_secondary_color'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-group">
                                            <strong><?php esc_html_e("Progress Bar", 'bookme') ?></strong>
                                            <div class="form-toggle-option" style="max-width: 100%">
                                                <div>
                                                    <label for="bookme_show_progress_bar"><?php esc_html_e('Show', 'bookme') ?></label>
                                                </div>
                                                <div>
                                                    <input type="hidden" name="bookme_show_progress_bar"
                                                           value="0">
                                                    <label class="switch switch-sm">
                                                        <input name="bookme_show_progress_bar"
                                                               type="checkbox"
                                                               id="bookme_show_progress_bar"
                                                               value="1" <?php checked(get_option('bookme_show_progress_bar'), 1) ?>>
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <strong><?php esc_html_e("Service duration next to service name", 'bookme') ?></strong>
                                            <div class="form-toggle-option" style="max-width: 100%">
                                                <div>
                                                    <label for="bookme_service_name_with_duration"><?php esc_html_e('Show', 'bookme') ?></label>
                                                </div>
                                                <div>
                                                    <input type="hidden"
                                                           name="bookme_service_name_with_duration" value="0">
                                                    <label class="switch switch-sm">
                                                        <input name="bookme_service_name_with_duration"
                                                               type="checkbox"
                                                               id="bookme_service_name_with_duration"
                                                               value="1" <?php checked(get_option('bookme_service_name_with_duration'), 1) ?>>
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <strong><?php esc_html_e("Service price next to employee name", 'bookme') ?></strong>
                                            <div class="form-toggle-option" style="max-width: 100%">
                                                <div>
                                                    <label for="bookme_employee_name_with_price"><?php esc_html_e('Show', 'bookme') ?></label>
                                                </div>
                                                <div>
                                                    <input type="hidden" name="bookme_employee_name_with_price"
                                                           value="0">
                                                    <label class="switch switch-sm">
                                                        <input name="bookme_employee_name_with_price"
                                                               type="checkbox"
                                                               id="bookme_employee_name_with_price"
                                                               value="1" <?php checked(get_option('bookme_employee_name_with_price'), 1) ?>>
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <strong><?php esc_html_e('Form Layout', 'bookme') ?></strong>
                                            <select id="bookme_form_layout" class="form-control"
                                                    name="bookme_form_layout">
                                                <option value="1"
                                                    <?php selected(get_option('bookme_form_layout'), 1) ?>>
                                                    <?php esc_attr_e('One Column', 'bookme'); ?>
                                                </option>
                                                <option value="2"
                                                    <?php selected(get_option('bookme_form_layout'), 2) ?>>
                                                    <?php esc_attr_e('Two Column', 'bookme'); ?>
                                                </option>
                                            </select>
                                        </div>
                                        <hr>
                                        <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
                                        <button type="submit"
                                                class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
                                        <button type="button" id="bookme-custom-css-button"
                                                class="btn btn-default float-right"><?php esc_html_e('Custom CSS', 'bookme') ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="bookme-card card">
                                <div class="card-body">
                                    <?php include 'booking-form.php' ?>
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

    <div id="bookme-custom-css-panel"
         class="slidePanel <?php echo is_rtl() ? 'slidePanel-left' : 'slidePanel-right'; ?>">
        <div class="slidePanel-scrollable">
            <div>
                <div class="slidePanel-content">
                    <header class="slidePanel-header">
                        <div class="slidePanel-overlay-panel">
                            <div class="slidePanel-heading">
                                <h2><?php esc_html_e('Edit custom CSS', 'bookme') ?></h2>
                            </div>
                            <div class="slidePanel-actions">
                                <button type="button" class="btn-icon btn-primary bm-save-custom-css" title="<?php esc_attr_e('Save', 'bookme') ?>">
                                    <i class="icon-feather-check"></i>
                                </button>
                                <button type="button" class="btn-icon slidePanel-close" title="<?php esc_attr_e('Close', 'bookme') ?>">
                                    <i class="icon-feather-x"></i>
                                </button>
                            </div>
                        </div>
                    </header>
                    <div class="slidePanel-inner">
                        <div class="form-group theme-form">
                            <label for="bookme-custom-css-field"
                                   class="control-label"><?php esc_html_e('Custom CSS', 'bookme') ?></label>
                            <textarea id="bookme-custom-css-field" class="form-control"
                                      rows="5"><?php echo $custom_css ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>