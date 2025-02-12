<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\Inc\Mains\Tables\CustomerBooking;

?>
<div id="bm-customer-details-panel" class="slidePanel <?php echo is_rtl() ? 'slidePanel-left' : 'slidePanel-right'; ?>">
    <div class="slidePanel-scrollable">
        <div>
            <div class="slidePanel-content">
                <header class="slidePanel-header">
                    <div class="slidePanel-overlay-panel">
                        <div class="slidePanel-heading">
                            <h2><?php esc_html_e('Edit customer details', 'bookme') ?></h2>
                        </div>
                        <div class="slidePanel-actions">
                            <div class="btn-group-flat">
                                <button class="btn-icon btn-primary bm-save-customer-details"
                                        title="<?php esc_attr_e('Save', 'bookme') ?>">
                                    <i class="icon-feather-check"></i>
                                </button>
                                <button class="btn-icon slidePanel-close"
                                        title="<?php esc_attr_e('Close', 'bookme') ?>">
                                    <i class="icon-feather-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="slidePanel-inner">
                    <form class="theme-form">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="bookme-booking-status"><?php esc_html_e('Status', 'bookme') ?></label>
                                <select id="bookme-booking-status" class="bookme-custom-field form-control">
                                    <option value="<?php echo CustomerBooking::STATUS_PENDING ?>"><?php echo esc_html(CustomerBooking::status_to_string(CustomerBooking::STATUS_PENDING)) ?></option>
                                    <option value="<?php echo CustomerBooking::STATUS_APPROVED ?>"><?php echo esc_html(CustomerBooking::status_to_string(CustomerBooking::STATUS_APPROVED)) ?></option>
                                    <option value="<?php echo CustomerBooking::STATUS_CANCELLED ?>"><?php echo esc_html(CustomerBooking::status_to_string(CustomerBooking::STATUS_CANCELLED)) ?></option>
                                    <option value="<?php echo CustomerBooking::STATUS_REJECTED ?>"><?php echo esc_html(CustomerBooking::status_to_string(CustomerBooking::STATUS_REJECTED)) ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="bookme-nop-field"><?php esc_html_e('Number of persons', 'bookme') ?></label>
                                <select id="bookme-nop-field" class="bookme-custom-field form-control"></select>
                            </div>
                            <div id="bookme-custom-fields-wrapper">
                                <hr>
                                <h5 class="text-secondary"><?php esc_html_e('Custom Fields', 'bookme') ?></h5>
                                <?php foreach ($custom_fields as $custom_field) { ?>
                                    <div class="form-group"
                                         data-type="<?php echo esc_attr($custom_field->type) ?>"
                                         data-id="<?php echo esc_attr($custom_field->id) ?>"
                                         data-services="<?php echo esc_attr(json_encode($custom_field->services)) ?>">
                                        <label for="custom_field_<?php echo esc_attr($custom_field->id) ?>"><?php echo $custom_field->label ?></label>
                                        <div>
                                            <?php if ($custom_field->type == 'text-field') { ?>
                                                <input id="custom_field_<?php echo esc_attr($custom_field->id) ?>"
                                                       type="text"
                                                       class="bookme-custom-field form-control"/>

                                            <?php } elseif ($custom_field->type == 'textarea') { ?>
                                                <textarea id="custom_field_<?php echo esc_attr($custom_field->id) ?>"
                                                        rows="2"
                                                        class="bookme-custom-field form-control"></textarea>

                                            <?php } elseif ($custom_field->type == 'checkboxes') { ?>
                                                <?php foreach ($custom_field->items as $i => $item) {
                                                    $id = uniqid($i);
                                                    ?>
                                                    <div class="checkbox m-b-10">
                                                        <input type="checkbox" id="check_<?php echo $id ?>" value="<?php echo esc_attr($item) ?>"
                                                               class="bookme-custom-field">
                                                        <label for="check_<?php echo $id ?>"><span class="checkbox-icon"></span> <?php echo $item ?></label>
                                                    </div><br>
                                                <?php } ?>

                                            <?php } elseif ($custom_field->type == 'radio-buttons') { ?>
                                                <?php foreach ($custom_field->items as $item) { ?>
                                                    <div class="radio">
                                                        <label>
                                                            <input type="radio"
                                                                   name="<?php echo $custom_field->id ?>"
                                                                   class="bookme-custom-field"
                                                                   value="<?php echo esc_attr($item) ?>"/>
                                                            <?php echo $item ?>
                                                        </label>
                                                    </div>
                                                <?php } ?>

                                            <?php } elseif ($custom_field->type == 'drop-down') { ?>
                                                <select id="custom_field_<?php echo esc_attr($custom_field->id) ?>"
                                                        class="bookme-custom-field form-control">
                                                    <option value=""></option>
                                                    <?php foreach ($custom_field->items as $item) { ?>
                                                        <option value="<?php echo esc_attr($item) ?>"><?php echo $item ?></option>
                                                    <?php } ?>
                                                </select>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>