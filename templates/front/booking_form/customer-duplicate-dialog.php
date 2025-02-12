<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<div class="bookme-modal bookme-modal-hide bookme-customer-modal">
    <div class="bookme-modal-dialog bookme-booking-form">
        <div class="bookme-modal-content">
            <div class="bookme-modal-header">
                <div><?php esc_html_e( 'Data already in use', 'bookme' ) ?><button type="button" class="bookme-modal-close bookme-modal-dismiss">×</button></div>
            </div>
            <div class="bookme-modal-body"></div>
            <div class="bookme-modal-actions">
                <button class="bookme-modal-submit bookme-button" type="submit"><?php esc_html_e( 'Update' ) ?></button>
                <a href="#" class="bookme-modal-cancel bookme-modal-dismiss"><?php esc_html_e( 'Cancel' ) ?></a>
            </div>
        </div>
    </div>
</div>