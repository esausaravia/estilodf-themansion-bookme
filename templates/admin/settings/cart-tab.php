<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<form class="theme-form bm-ajax-form" data-tab="cart">
    <div class="bookme-card card">
        <div class="card-header">
            <h5><?php esc_html_e('Cart', 'bookme') ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="bookme_cart_enabled"><?php esc_html_e('Cart', 'bookme') ?> <i class="dashicons dashicons-editor-help" title="<?php esc_attr_e('Give the facility to your client to book multiple appointments at once.', 'bookme') ?>" data-tippy-placement="top"></i></label>
                        <div class="form-toggle-option">
                            <div>
                                <label for="bookme_cart_enabled"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="bookme_cart_enabled" value="0">
                                <label class="switch switch-sm">
                                    <input name="bookme_cart_enabled" type="checkbox" id="bookme_cart_enabled"  value="1" <?php checked(get_option('bookme_cart_enabled'),1) ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <label><?php esc_attr_e('Columns', 'bookme') ?></label><br/>
                        <div id="bookme-cart-columns">
                            <?php foreach ((array)get_option('bookme_cart_columns') as $column => $attr) { ?>
                                <div class="form-toggle-option m-b-5">
                                    <div>
                                        <i class="icon-feather-menu bookme-reorder-icon"
                                           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                                        <label for="bookme_cart_columns[<?php echo $column ?>][show]"><?php echo isset($cart_columns[$column]) ? $cart_columns[$column] : '' ?></label>
                                    </div>
                                    <div>
                                        <input type="hidden" name="bookme_cart_columns[<?php echo $column ?>][show]"
                                               value="0">
                                        <label class="switch switch-sm">
                                            <input id="bookme_cart_columns[<?php echo $column ?>][show]" name="bookme_cart_columns[<?php echo $column ?>][show]" type="checkbox"  value="1" <?php checked($attr['show'], true) ?>>
                                            <span class="switch-state"></span>
                                        </label>
                                    </div>
                                </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <?php \Bookme\Inc\Mains\Functions\System::csrf() ?>
            <button type="submit" class="btn btn-primary"><?php esc_html_e('Save', 'bookme') ?></button>
        </div>
    </div>
</form>