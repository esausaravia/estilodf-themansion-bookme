<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

echo $progress_bar; ?>
<div class="bookme-booking-time-step">
    <?php
    if (!empty($slots)) {
        foreach ($slots as $group => $group_slots) {
            /** @var \Bookme\Inc\Mains\Availability\TimeSlot[] $group_slots */
            $service = \Bookme\Inc\Mains\Tables\Service::find($group_slots[0]->service_id());
            $staff = \Bookme\Inc\Mains\Tables\Employee::find($group_slots[0]->staff_id());
            ?>
            <div class="bookme-booking-time-step-heading">
                <h2><?php echo date_i18n(($duration_in_days ? 'F Y' : get_option('date_format')), strtotime($group)) ?></h2>
                <p><?php echo strtr(
                        __('Available time slots for <strong>{service_title}</strong> by <strong>{employee_name}</strong>.', 'bookme'),
                        array(
                            '{service_title}' => $service->get_translated_title(),
                            '{employee_name}' => $staff->get_translated_name()
                        )
                    ); ?></p>
                <div class="bookme-form-error"></div>
            </div>
            <div  class="tse-scrollable bookme-timeslot-scroll">
                <div class="tse-content">
                    <?php
                    foreach ($group_slots as $slot) {
                        $data = $slot->build_slot_data();
                        $slot = $slot->resize($service->get_duration());
                        $time = $slot->start()->to_client_tz()->format_i18n(
                                ($duration_in_days
                                    ? 'D, M d'
                                    : get_option('time_format'))
                            ) . (!get_option('bookme_hide_booking_end_time')
                                ? ' - ' . $slot->end()->to_client_tz()->format_i18n(
                                    ($duration_in_days
                                        ? 'D, M d'
                                        : get_option('time_format'))
                                )
                                : '');
                        ?>
                        <div class="bookme-timeslot<?php echo $data[0][2] == $selected_date ? ' bookme-timeslot-selected' : ''; ?>">
                            <div class="bookme-timeslot-time">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <span><?php echo $time; ?></span>
                            </div>
                            <button type="button" class="bookme-button bookme-timeslot-button" value="<?php echo esc_attr(json_encode($data)) ?>">
                                <span class="bookme-timeslot-button-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <span><?php echo $time; ?></span>
                                </span>
                                <span class="bookme-timeslot-button-text"><?php esc_html_e('Book Now', 'bookme') ?></span>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
    } else { ?>
        <p><?php esc_html_e('No time slot is available for selected details.', 'bookme') ?></p>
    <?php } ?>
    <div class="bookme-step-buttons">
        <div class="bookme-step-buttons-left">
            <button type="button" class="bookme-button bookme-back"><?php esc_html_e('Back', 'bookme') ?></button>
            <?php if ($show_cart) { ?>
            <button type="button" class="bookme-button bookme-icon-button bookme-cart"
                    title="<?php esc_html_e('Cart', 'bookme') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-shopping-cart">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </button>
            <?php } ?>
        </div>
    </div>
</div>