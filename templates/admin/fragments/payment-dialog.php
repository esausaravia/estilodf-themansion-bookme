<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<div id="bookme-payment-details-dialog"
     class="slidePanel <?php echo is_rtl() ? 'slidePanel-left' : 'slidePanel-right'; ?>">
    <div class="slidePanel-scrollable">
        <div>
            <div class="slidePanel-content">
                <header class="slidePanel-header">
                    <div class="slidePanel-overlay-panel">
                        <div class="slidePanel-heading">
                            <h2><?php esc_html_e('Payment', 'bookme') ?></h2>
                        </div>
                        <div class="slidePanel-actions">
                            <button class="btn-icon btn-primary bm-payment-print" title="<?php esc_attr_e('Print', 'bookme') ?>">
                                <i class="icon-feather-printer"></i>
                            </button>
                            <button class="btn-icon slidePanel-close" title="<?php esc_attr_e('Close', 'bookme') ?>">
                                <i class="icon-feather-x"></i>
                            </button>
                        </div>
                    </div>
                </header>
                <div class="slidePanel-inner">
                    <div class="bookme-loading"></div>
                    <div class="payment-dialog-body"></div>
                </div>
            </div>
        </div>
    </div>
</div>