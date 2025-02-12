<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<div class="bookme-row">
    <div class="bookme-col-sm-8 bookme-form-group">
        <label><?php esc_html_e('Card Number', 'bookme') ?></label>
        <input type="text" name="card_number" autocomplete="off"/>
    </div>
    <div class="bookme-col-sm-4 bookme-form-group">
        <label><?php esc_html_e('CVC', 'bookme') ?></label>
        <input type="text" class="bookme-card-cvc" name="card_cvc" autocomplete="off"/>
    </div>
</div>
<div class="bookme-form-group">
    <label><?php esc_html_e('Expiration Date', 'bookme') ?></label>
    <div class="bookme-row">
        <div class="bookme-col-6">
            <select class="bookme-card-exp" name="card_exp_month">
                <?php for ($i = 1; $i <= 12; ++$i) { ?>
                    <option value="<?php echo $i ?>"><?php printf('%02d', $i) ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="bookme-col-6">
            <select class="bookme-card-exp" name="card_exp_year">
                <?php for ($i = date('Y'); $i <= date('Y') + 10; ++$i) { ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
</div>
<div class="bookme-form-error bookme-card-error"></div>