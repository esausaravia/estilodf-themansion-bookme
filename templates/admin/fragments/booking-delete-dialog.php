<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<div class="modal fade" id="bm-booking-delete-dialog" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="icon-feather-alert-circle"></i> <?php esc_html_e("Delete",'bookme') ?></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body theme-form">
                <div class="checkbox m-b-10">
                    <input type="checkbox" id="bm-delete-notify">
                    <label for="bm-delete-notify"><span class="checkbox-icon"></span> <?php esc_html_e( 'Send notifications', 'bookme' ) ?></label>
                </div>
                <div id="bm-reason-wrapper" style="display: none;">
                    <label for="bm-cancel-reason"><?php esc_html_e( 'Cancellation reason (optional)', 'bookme' ) ?></label>
                    <input class="form-control" type="text" id="bm-cancel-reason" />
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" type="button"><?php esc_html_e('Cancel', 'bookme') ?></button>
                <button class="btn btn-danger bm-booking-delete" type="button"><?php esc_html_e('Delete', 'bookme') ?></button>
            </div>
        </div>
    </div>
</div>