<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\App\Admin\Fragments; ?>
<div class="bookme-page-wrapper">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
        <?php Fragments::render_header(); ?>
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <?php Fragments::render_sidebar_menu('custom-fields') ?>
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-lg-6 main-header">
                                <h2><?php esc_html_e('Custom Fields', 'bookme') ?></h2>
                                <h6 class="mb-0"><?php esc_html_e('admin panel', 'bookme') ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="bookme-card card">
                        <div class="card-header">
                            <div class="row theme-form">
                                <div class="col-sm-6">
                                    <label for="bookme_custom_fields_per_service"><?php esc_html_e('Custom fields per service', 'bookme') ?>
                                        <i class="dashicons dashicons-editor-help"
                                           title="<?php esc_attr_e('Create service specific custom fields.', 'bookme') ?>"
                                           data-tippy-placement="top"></i>
                                    </label>
                                    <div class="form-toggle-option" style="max-width: 100%">
                                        <div>
                                            <label for="bookme_custom_fields_per_service"><?php esc_html_e('Enable', 'bookme') ?></label>
                                        </div>
                                        <div>
                                            <label class="switch switch-sm">
                                                <input name="bookme_custom_fields_per_service" type="checkbox"
                                                       id="bookme_custom_fields_per_service"
                                                       value="1" <?php checked(get_option('bookme_custom_fields_per_service'), 1) ?>>
                                                <span class="switch-state"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 bm-service-field" style="display: none">
                                    <label for="bookme_custom_fields_merge"><?php esc_html_e('Merge custom fields for multiple bookings of a service', 'bookme') ?>
                                        <i class="dashicons dashicons-editor-help"
                                           title="<?php esc_attr_e('If disabled, custom fields will appear for each appointment in the set of bookings (in cart booking)', 'bookme') ?>"
                                           data-tippy-placement="top"></i>
                                    </label>
                                    <div class="form-toggle-option" style="max-width: 100%">
                                        <div>
                                            <label for="bookme_custom_fields_merge"><?php esc_html_e('Enable', 'bookme') ?></label>
                                        </div>
                                        <div>
                                            <label class="switch switch-sm">
                                                <input name="bookme_custom_fields_merge" type="checkbox"
                                                       id="bookme_custom_fields_merge"
                                                       value="1" <?php checked(get_option('bookme_custom_fields_merge'), 1) ?>>
                                                <span class="switch-state"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="bm-add-fields">
                                <button class="btn btn-default m-b-10 m-r-10" data-type="text-field">
                                    <i class="icon-feather-plus"></i> <?php esc_html_e('Text Field', 'bookme') ?>
                                </button>
                                <button class="btn btn-default m-b-10 m-r-10" data-type="textarea"><i
                                            class="icon-feather-plus"></i> <?php esc_html_e('Text Area', 'bookme') ?>
                                </button>
                                <button class="btn btn-default m-b-10 m-r-10"
                                        data-type="text-content"><i
                                            class="icon-feather-plus"></i> <?php esc_html_e('Text Content', 'bookme') ?>
                                </button>
                                <button class="btn btn-default m-b-10 m-r-10" data-type="checkboxes">
                                    <i class="icon-feather-plus"></i> <?php esc_html_e('Checkboxes', 'bookme') ?>
                                </button>
                                <button class="btn btn-default m-b-10 m-r-10"
                                        data-type="radio-buttons"><i
                                            class="icon-feather-plus"></i> <?php esc_html_e('Radio Buttons', 'bookme') ?>
                                </button>
                                <button class="btn btn-default m-b-10 m-r-10" data-type="drop-down"><i
                                            class="icon-feather-plus"></i> <?php esc_html_e('Drop Down', 'bookme') ?>
                                </button>
                            </div>
                            <hr>
                            <div id="bm-custom-fields">
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="d-none d-sm-inline" style="line-height: 35px;"><?php esc_html_e('HTML allowed in all texts and labels.', 'bookme') ?></span>
                            <button id="ajax-save-custom-fields" class="btn btn-primary float-right"><?php esc_html_e('Save', 'bookme') ?></button>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid Ends-->
            </div>
            <?php Fragments::render_footer() ?>
        </div>
    </div>

    <!-- templates -->
    <div id="bm-custom-fields-templates" hidden>
        <div class="bm-cf bm-custom-field" data-type="text-field">
            <h5>
                <i class="icon-feather-menu bookme-reorder-icon"
                   title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                <?php esc_html_e('Text Field', 'bookme') ?>
                <a href="javascript:void(0)" class="bm-custom-field-delete float-right" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a>
            </h5>
            <div class="row theme-form align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <input type="text" autocomplete="off" placeholder="<?php esc_attr_e('Label', 'bookme') ?>"
                           class="form-control bm-label">
                </div>
                <div class="col-md-4 col-6 bm-service-field" style="display: none">
                    <?php include 'services-field.php'; ?>
                </div>
                <div class="col-md-2 col-6">
                    <div class="checkbox">
                        <input type="checkbox" class="bm-required">
                        <label><span class="checkbox-icon"></span>  <?php esc_html_e('Required', 'bookme') ?></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="bm-cf bm-custom-field" data-type="textarea">
            <h5>
                <i class="icon-feather-menu bookme-reorder-icon"
                   title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                <?php esc_html_e('Text Area', 'bookme') ?>
                <a href="javascript:void(0)" class="bm-custom-field-delete float-right" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a>
            </h5>
            <div class="row theme-form align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <input type="text" autocomplete="off" placeholder="<?php esc_attr_e('Label', 'bookme') ?>"
                           class="form-control bm-label">
                </div>
                <div class="col-md-4 col-6 bm-service-field" style="display: none">
                    <?php include 'services-field.php'; ?>
                </div>
                <div class="col-md-2 col-6">
                    <div class="checkbox">
                        <input type="checkbox" class="bm-required">
                        <label><span class="checkbox-icon"></span>  <?php esc_html_e('Required', 'bookme') ?></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="bm-cf bm-custom-field" data-type="text-content">
            <h5>
                <i class="icon-feather-menu bookme-reorder-icon"
                   title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                <?php esc_html_e('Text Content', 'bookme') ?>
                <a href="javascript:void(0)" class="bm-custom-field-delete float-right" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a>
            </h5>
            <div class="row theme-form align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <textarea autocomplete="off" class="form-control bm-label" rows="3"
                              placeholder="<?php esc_attr_e('Content', 'bookme') ?>"></textarea>
                </div>
                <div class="col-md-4 col-6 bm-service-field" style="display: none">
                    <?php include 'services-field.php'; ?>
                </div>
                <div class="col-md-2 col-6">
                    <div class="checkbox">
                        <input type="checkbox" class="bm-required">
                        <label><span class="checkbox-icon"></span>  <?php esc_html_e('Required', 'bookme') ?></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="bm-cf bm-custom-field" data-type="checkboxes">
            <h5>
                <i class="icon-feather-menu bookme-reorder-icon"
                   title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                <?php esc_html_e('Checkboxes', 'bookme') ?>
                <a href="javascript:void(0)" class="bm-custom-field-delete float-right" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a>
            </h5>
            <div class="row theme-form align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <input type="text" autocomplete="off" placeholder="<?php esc_attr_e('Label', 'bookme') ?>" class="form-control bm-label">
                </div>
                <div class="col-md-4 col-6 bm-service-field" style="display: none">
                    <?php include 'services-field.php'; ?>
                </div>
                <div class="col-md-2 col-6">
                    <div class="checkbox">
                        <input type="checkbox" class="bm-required">
                        <label><span class="checkbox-icon"></span>  <?php esc_html_e('Required', 'bookme') ?></label>
                    </div>
                </div>
            </div>
            <div class="bm-items"></div>
            <button class="btn btn-default bm-custom-field-item m-t-20" data-type="checkboxes-item">
                <i class="icon-feather-plus"></i> <?php esc_html_e('Option', 'bookme') ?>
            </button>
        </div>
        <div class="bm-cf m-t-20" data-type="checkboxes-item">
            <div class="d-flex theme-form align-items-center">
                <div class="m-r-5">
                    <i class="icon-feather-menu bookme-reorder-icon"
                       title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                </div>
                <div>
                    <input type="text" autocomplete="off" placeholder="<?php esc_attr_e('Option', 'bookme') ?>" class="form-control">
                </div>
                <div>
                    <h5 class="m-b-0 m-l-5"><a href="javascript:void(0)" class="bm-custom-field-delete" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a></h5>
                </div>
            </div>
        </div>
        <div class="bm-cf bm-custom-field" data-type="radio-buttons">
            <h5>
                <i class="icon-feather-menu bookme-reorder-icon"
                   title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                <?php esc_html_e('Radio Buttons', 'bookme') ?>
                <a href="javascript:void(0)" class="bm-custom-field-delete float-right" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a>
            </h5>
            <div class="row theme-form align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <input type="text" autocomplete="off" placeholder="<?php esc_attr_e('Label', 'bookme') ?>"
                           class="form-control bm-label">
                </div>
                <div class="col-md-4 col-6 bm-service-field" style="display: none">
                    <?php include 'services-field.php'; ?>
                </div>
                <div class="col-md-2 col-6">
                    <div class="checkbox">
                        <input type="checkbox" class="bm-required">
                        <label><span class="checkbox-icon"></span>  <?php esc_html_e('Required', 'bookme') ?></label>
                    </div>
                </div>
            </div>
            <div class="bm-items"></div>
            <button class="btn btn-default bm-custom-field-item m-t-20" data-type="radio-buttons-item">
                <i class="icon-feather-plus"></i> <?php esc_html_e('Option', 'bookme') ?>
            </button>
        </div>
        <div class="bm-cf m-t-20" data-type="radio-buttons-item">
            <div class="d-flex theme-form align-items-center">
                <div class="m-r-5">
                    <i class="icon-feather-menu bookme-reorder-icon"
                       title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                </div>
                <div>
                    <input type="text" autocomplete="off" placeholder="<?php esc_attr_e('Option', 'bookme') ?>" class="form-control">
                </div>
                <div>
                    <h5 class="m-b-0 m-l-5"><a href="javascript:void(0)" class="bm-custom-field-delete" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a></h5>
                </div>
            </div>
        </div>
        <div class="bm-cf bm-custom-field" data-type="drop-down">
            <h5>
                <i class="icon-feather-menu bookme-reorder-icon"
                   title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                <?php esc_html_e('Drop Down', 'bookme') ?>
                <a href="javascript:void(0)" class="bm-custom-field-delete float-right" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a>
            </h5>
            <div class="row theme-form align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <input type="text" autocomplete="off" placeholder="<?php esc_attr_e('Label', 'bookme') ?>"
                           class="form-control bm-label">
                </div>
                <div class="col-md-4 col-6 bm-service-field" style="display: none">
                    <?php include 'services-field.php'; ?>
                </div>
                <div class="col-md-2 col-6">
                    <div class="checkbox">
                        <input type="checkbox" class="bm-required">
                        <label><span class="checkbox-icon"></span>  <?php esc_html_e('Required', 'bookme') ?></label>
                    </div>
                </div>
            </div>
            <div class="bm-items"></div>
            <button class="btn btn-default bm-custom-field-item m-t-20" data-type="drop-down-item">
                <i class="icon-feather-plus"></i> <?php esc_html_e('Option', 'bookme') ?>
            </button>
        </div>
        <div class="bm-cf m-t-20" data-type="drop-down-item">
            <div class="d-flex theme-form align-items-center">
                <div class="m-r-5">
                    <i class="icon-feather-menu bookme-reorder-icon"
                       title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                </div>
                <div>
                    <input type="text" autocomplete="off" placeholder="<?php esc_attr_e('Option', 'bookme') ?>" class="form-control">
                </div>
                <div>
                    <h5 class="m-b-0 m-l-5"><a href="javascript:void(0)" class="bm-custom-field-delete" title="<?php esc_attr_e('Delete', 'bookme') ?>"><i class="icon-feather-trash-2"></i></a></h5>
                </div>
            </div>
        </div>
    </div>
</div>