<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

echo $progress_bar; ?>
<div class="bookme-booking-done-step" style="text-align: center;">
    <div><?php echo esc_html($message) ?></div>
    <button type="button" class="bookme-button" onclick="location.href='<?php echo esc_url($page_url); ?>'"><?php esc_html_e('New Booking','bookme'); ?></button>
</div>