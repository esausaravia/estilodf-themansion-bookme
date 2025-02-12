<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
use Bookme\Inc\Mains\Functions;
use \Bookme\Inc\Mains\Tables\CustomerBooking;

foreach ($bookings as $app) { ?>
    <tr>
        <?php foreach ($columns as $column) {
            switch ($column) {
                case 'service' : ?>
                    <td>
                    <?php echo $app['service'] ?>
                    </td><?php
                    break;
                case 'date' : ?>
                    <td><?php echo Functions\DateTime::format_date($app['start_date']) ?></td><?php
                    break;
                case 'time' : ?>
                    <td><?php echo Functions\DateTime::format_time($app['start_date']) ?></td><?php
                    break;
                case 'price' : ?>
                    <td><?php echo Functions\Price::format(($app['price'])) ?></td><?php
                    break;
                case 'status' : ?>
                    <td><?php echo CustomerBooking::status_to_string($app['booking_status']) ?></td><?php
                    break;
                case 'cancel' :
                    include 'custom_fields.php'; ?>
                    <td>
                    <?php if ($app['start_date'] > current_time('mysql')) {
                    if ($allow_cancel < strtotime($app['start_date'])) {
                        if (
                            ($app['booking_status'] != CustomerBooking::STATUS_CANCELLED)
                            && ($app['booking_status'] != CustomerBooking::STATUS_REJECTED)
                        ) { ?>
                            <a class="bookme-button bookme-cancel-booking"
                               href="<?php echo esc_attr($cancel_url . '&token=' . $app['token']) ?>">
                                <?php esc_html_e('Cancel', 'bookme') ?>
                            </a>
                        <?php }
                    } else {
                        esc_html_e('Not allowed', 'bookme');
                    }
                } else {
                    esc_html_e('Expired', 'bookme');
                } ?>
                    </td><?php
                    break;
                default : ?>
                    <td><?php echo $app[$column] ?></td>
                <?php }
        }
        if ($with_cancel == false) {
            include 'custom_fields.php';
        } ?>
    </tr>
<?php }
