<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access


$subtotal = 0;
$subtotal_deposit = 0;
?>
<?php if ($payment) { ?>
    <div class="table-responsive m-b-20">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th><?php esc_html_e('Customer', 'bookme') ?></th>
                <th><?php esc_html_e('Payment', 'bookme') ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo $payment['customer'] ?></td>
                <td>
                    <div><?php esc_html_e('Date', 'bookme') ?>
                        : <?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date_time($payment['created']) ?></div>
                    <div><?php esc_html_e('Type', 'bookme') ?>
                        : <?php echo \Bookme\Inc\Mains\Tables\Payment::type_to_string($payment['type']) ?></div>
                    <div><?php esc_html_e('Status', 'bookme') ?>
                        : <?php echo \Bookme\Inc\Mains\Tables\Payment::status_to_string($payment['status']) ?></div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th><?php esc_html_e('Date', 'bookme') ?></th>
                <th><?php echo esc_attr(\Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_service')) ?></th>
                <th><?php echo esc_attr(\Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_employee')) ?></th>
                <th class="text-right"><?php esc_html_e('Price', 'bookme') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item) { ?>
                <tr>
                    <td><?php echo \Bookme\Inc\Mains\Functions\DateTime::format_date_time($item['booking_date']) ?></td>
                    <td>
                        <?php if ($item['number_of_persons'] > 1) echo $item['number_of_persons'] . '&nbsp;&times;&nbsp;' ?><?php echo $item['service_name'] ?>
                    </td>
                    <td><?php echo $item['staff_name'] ?></td>
                    <td class="text-right">
                        <?php $service_price = \Bookme\Inc\Mains\Functions\Price::format($item['service_price']) ?>
                        <?php if ($item['number_of_persons'] > 1) $service_price = $item['number_of_persons'] . '&nbsp;&times;&nbsp;' . $service_price ?>
                        <?php echo $service_price ?>
                    </td>
                </tr>
                <?php $subtotal += $item['number_of_persons'] * $item['service_price'] ?>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <th rowspan="3"></th>
                <th colspan="2"><?php esc_html_e('Subtotal', 'bookme') ?></th>
                <th class="text-right"><?php echo \Bookme\Inc\Mains\Functions\Price::format($subtotal) ?></th>
            </tr>
            <tr>
                <th colspan="2">
                    <?php esc_html_e('Discount', 'bookme') ?>
                    <?php if ($payment['coupon']) { ?>
                        <div>
                        <small>(<?php echo $payment['coupon']['code'] ?>)</small></div><?php } ?>
                </th>
                <th class="text-right">
                    <?php if ($payment['coupon']) { ?>
                        <?php if ($payment['coupon']['discount']) { ?>
                            <div>-<?php echo $payment['coupon']['discount'] ?>%</div>
                        <?php } ?>
                        <?php if ($payment['coupon']['deduction']) { ?>
                            <div><?php echo \Bookme\Inc\Mains\Functions\Price::format(-$payment['coupon']['deduction']) ?></div>
                        <?php } ?>
                    <?php } else { ?>
                        <?php echo \Bookme\Inc\Mains\Functions\Price::format(0) ?>
                    <?php } ?>
                </th>
            </tr>
            <tr>
                <th colspan="2"><?php esc_html_e('Total', 'bookme') ?></th>
                <th class="text-right"><?php echo \Bookme\Inc\Mains\Functions\Price::format($payment['total']) ?></th>
            </tr>
            <?php if ($payment['total'] != $payment['paid']) { ?>
                <tr>
                    <td></td>
                    <td colspan="3" class="text-right">
                        <button type="button" class="btn btn-primary"
                                id="bookme-complete-payment"><?php esc_html_e('Complete payment', 'bookme') ?></button>
                    </td>
                </tr>
            <?php } ?>
            </tfoot>
        </table>
    </div>
<?php } ?>