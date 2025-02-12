<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<div id="bm-coupon-sidepanel" class="slidePanel <?php echo is_rtl() ? 'slidePanel-left' : 'slidePanel-right'; ?>">
    <div class="slidePanel-scrollable">
        <div>
            <div class="slidePanel-content">
                <header class="slidePanel-header">
                    <div class="slidePanel-overlay-panel">
                        <div class="slidePanel-heading">
                            <h2 id="bm-add-coupon-title"><?php esc_html_e('New coupon', 'bookme'); ?></h2>
                            <h2 id="bm-edit-coupon-title"><?php esc_html_e('Edit coupon', 'bookme'); ?></h2>
                        </div>
                        <div class="slidePanel-actions">
                            <button id="ajax-save-coupon" class="btn-icon btn-primary" title="<?php esc_attr_e('Save', 'bookme') ?>">
                                <i class="icon-feather-check"></i>
                            </button>
                            <button class="btn-icon slidePanel-close" title="<?php esc_attr_e('Close', 'bookme') ?>">
                                <i class="icon-feather-x"></i>
                            </button>
                        </div>
                    </div>
                </header>
                <div class="slidePanel-inner">
                    <form class="theme-form">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class=form-group>
                                    <label for="bm-code"><?php esc_html_e('Code', 'bookme') ?></label>
                                    <input type="text" id="bm-code" class="form-control"
                                           name="code"/>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class=form-group>
                                    <label><?php esc_html_e('Services', 'bookme') ?></label>
                                    <select name="service_ids[]" class="form-control bm-services" multiple data-placeholder="<?php esc_attr_e('Select Services', 'bookme') ?>" data-nothing="<?php esc_attr_e('No Service selected', 'bookme') ?>" data-selected="<?php esc_attr_e('selected', 'bookme') ?>" data-selectall="<?php esc_attr_e('Select All', 'bookme') ?>" data-unselectall="<?php esc_attr_e('Unselect All', 'bookme') ?>" data-allselected="<?php esc_attr_e('All Services Selected', 'bookme') ?>">
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class=form-group>
                                    <label for="bm-discount"><?php esc_html_e('Discount (%)', 'bookme') ?></label>
                                    <input type="number" id="bm-discount"
                                           class="form-control" name="discount"/>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class=form-group>
                                    <label for="bm-deduction"><?php esc_html_e('Deduction', 'bookme') ?></label>
                                    <input type="number" id="bm-deduction"
                                           class="form-control" name="deduction"/>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class=form-group>
                                    <label for="bm-usage-limit"><?php esc_html_e('Usage limit', 'bookme') ?></label>
                                    <input type="number" id="bm-usage-limit"
                                           class="form-control" name="usage_limit" min="0"
                                           step="1"/>
                                </div>
                            </div>
                        </div>
                        <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>