<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\Inc\Mains\Functions;
use \Bookme\Inc\Mains\Tables\CustomerBooking;

$primary_color = get_option('bookme_primary_color', '#6B76FF');
$secondary_color = get_option('bookme_secondary_color', '#FFF');
$custom_fields = isset($attributes['custom_fields']) ? explode(',', $attributes['custom_fields']) : array();
$columns = isset($attributes['columns']) ? explode(',', $attributes['columns']) : array();
$with_cancel = in_array('cancel', $columns);
?>
<?php if (is_user_logged_in()) {
    include 'css.php'; ?>
    <div class="bookme-customer-bookings bookme-booking-form">
        <h2><?php esc_html_e('All Bookings', 'bookme') ?></h2>
        <?php if (!empty($columns) || !empty($custom_fields)) { ?>
            <table class="bookme-customer-bookings-table" data-columns="<?php echo esc_attr(json_encode($columns)) ?>"
                   data-custom_fields="<?php echo esc_attr(implode(',', $custom_fields)) ?>" data-page="0">
                <thead>
                <tr>
                    <?php foreach ($columns as $column) { ?>
                        <?php if ($column != 'cancel') { ?>
                            <th><?php echo esc_html($titles[$column]) ?></th>
                        <?php } ?>
                    <?php } ?>
                    <?php foreach ($custom_fields as $column) { ?>
                        <th><?php if (isset($titles[$column])) echo $titles[$column] ?></th>
                    <?php } ?>
                    <?php if ($with_cancel) { ?>
                        <th><?php echo esc_html($titles['cancel']) ?></th>
                    <?php } ?>
                </tr>
                </thead>
                <?php if (empty($bookings)) { ?>
                    <tr class="bookme-no-bookings">
                        <td colspan="<?php echo count($columns) + count($custom_fields) ?>"><?php esc_html_e('No bookings available.', 'bookme') ?></td>
                    </tr>
                <?php } else {
                    include 'rows.php';
                } ?>
            </table>
            <?php if ($more) { ?>
                <button class="bookme-button bookme-show-old-bookings">
                    <?php esc_html_e('Show old bookings', 'bookme') ?>
                </button>
            <?php } ?>
        <?php } ?>
    </div>

    <script type="text/javascript">
        (function ($) {
            window.bookmeCustomerBookings({
                ajaxurl: <?php echo json_encode($ajax_url) ?>
            });
        })(jQuery);
    </script>
<?php } else { ?>
    <?php wp_login_form() ?>
<?php } ?>