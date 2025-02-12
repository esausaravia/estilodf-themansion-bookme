<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

use Bookme\Inc\Mains\Functions\Price;

echo $progress_bar; ?>
<div class="bookme-booking-cart-step">
    <p><?php esc_html_e('List of services selected for booking.','bookme') ?></p>
    <p><?php esc_html_e("Click 'BOOK MORE' if you want to book more services.",'bookme') ?></p>
    <p><button type="button" class="bookme-button bookme-cart-add"><?php esc_html_e('BOOK MORE', 'bookme') ?></button></p>
    <p class="bookme-cart-error bookme-form-error"></p>

    <table>
        <thead class="bookme-desktop-table">
        <tr>
            <?php foreach ( $columns as $position => $column ) { ?>
                <th><?php echo $column ?></th>
            <?php } ?>
            <th><?php esc_html_e('Actions','bookme') ?></th>
        </tr>
        </thead>
        <tbody class="bookme-desktop-table">
        <?php foreach ( $items_data as $key => $data ) { ?>
            <tr data-key="<?php echo $key ?>" class="bookme-cart-tbody">
                <?php foreach ( $data as $position => $value ) { ?>
                    <td><?php echo $value ?></td>
                <?php } ?>
                <td>
                    <button type="button" class="bookme-button bookme-icon-button bookme-icon-button-sm bookme-cart-edit" title="<?php esc_html_e('Edit', 'bookme') ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button type="button" class="bookme-button bookme-icon-button bookme-icon-button-sm bookme-cart-delete" title="<?php esc_html_e('Delete', 'bookme') ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
        <tbody class="bookme-mobile-table">
        <?php foreach ( $items_data as $key => $data ) { ?>
            <?php foreach ( $data as $position => $value ) { ?>
                <tr data-key="<?php echo $key ?>" class="bookme-cart-tbody">
                    <th><?php echo $columns[ $position ] ?></th>
                    <td><?php echo $value ?></td>
                </tr>
            <?php } ?>
            <tr data-key="<?php echo $key ?>">
                <th></th>
                <td>
                    <button type="button" class="bookme-button bookme-icon-button bookme-icon-button-sm bookme-cart-edit" title="<?php esc_html_e('Edit', 'bookme') ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button type="button" class="bookme-button bookme-icon-button bookme-icon-button-sm bookme-cart-delete" title="<?php esc_html_e('Delete', 'bookme') ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
        <?php if ( isset( $positions['price'] )) { ?>
            <tfoot class="bookme-desktop-table">
            <tr>
                <?php foreach ( $columns as $position => $column ) { ?>
                    <td>
                        <?php if ( $position == 0 ) { ?>
                            <strong><?php esc_html_e( 'Total', 'bookme' ) ?>:</strong>
                        <?php } ?>
                        <?php if ( isset( $positions['price'] ) && $position == $positions['price'] ) { ?>
                            <strong class="bookme-cart-total"><?php echo Price::format( $total ) ?></strong>
                        <?php } ?>
                    </td>
                <?php } ?>
                <td></td>
            </tr>
            </tfoot>
            <tfoot class="bookme-mobile-table">
            <tr>
                <th><?php esc_html_e( 'Total', 'bookme' ) ?>:</th>
                <td><strong class="bookme-cart-total"><?php echo Price::format( $total ) ?></strong></td>
            </tr>
            </tfoot>
        <?php } ?>
    </table>

    <div class="bookme-step-buttons">
        <div class="bookme-step-buttons-left">
            <button type="button" class="bookme-button bookme-back"><?php esc_html_e('Back', 'bookme') ?></button>
        </div>
        <button type="button" class="bookme-button bookme-next"><?php esc_html_e('Next', 'bookme') ?></button>
    </div>
</div>