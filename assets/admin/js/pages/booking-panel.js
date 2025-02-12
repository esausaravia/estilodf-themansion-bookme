var $booking_panel = jQuery('#bm-booking-panel'),
    $form = $booking_panel.find('form'),
    $form_loader = jQuery('#bm-form-loader'),
    $form_wrapper = jQuery('#bm-form-wrapper'),
    $staff = jQuery("#bookme-staff"),
    $service = jQuery("#bookme-service"),
    $date = jQuery('#bookme-date'),
    $start_time = jQuery('#bookme-start-time'),
    $customer = jQuery('#bookme-customer'), // customer select list
    $customer_limit = jQuery('#bm-customer-limit'), // customer total / service capacity
    $customer_selector = jQuery('#bm-customer-selector'), // customer select list div
    $customers_list = jQuery('#bm-customers-list'), // customers list item
    $internal_note = jQuery('#bookme-internal-note'),
    $notification = jQuery('#bookme-notification'),
    $online_meeting_wrapper = jQuery('#bm-online-meeting-wrapper'),
    $online_meeting_url = jQuery('#bookme-online-meeting-url'),
    $online_meeting_url_link = jQuery('#bookme-online-meeting-url-link'),
    $online_meeting_pass = jQuery('#bookme-online-meeting-pass'),
    $errors = jQuery('.bm-errors'),
    current_customers_list = [],
    check_errors_ajax = false,
    callback;

function show_booking_panel(booking_id, staff_id, start_date, _callback) {
    $errors.slideUp();
    $booking_panel.find('.slidePanel-heading h2')
        .text(booking_id ? BookmeBooking.edit_booking : BookmeBooking.new_booking);

    callback = _callback;
    if (booking_id) {
        setup_edit_form(booking_id);
    } else {
        setup_new_form(staff_id, start_date);
    }

    // handle customer dialog
    jQuery('#bm-customer-sidepanel').on('sidePanel.hide', function () {
        jQuery('body').css('overflow-y', 'hidden');
    });

    bm_show_sidepanel($booking_panel);
}

function setup_new_form(staff_id, start_date) {
    $form_loader.hide();
    $form_wrapper.show();

    // reset fields
    check_errors_ajax = false;
    $form.data('id', null);
    $staff.val(staff_id).trigger('change');
    $service.val(staff_id && BookmeBooking.form.staff[staff_id].services.length == 1 ? BookmeBooking.form.staff[staff_id].services[0] : null).trigger('change');
    $date.datepicker('setDate', start_date.clone().local().toDate());
    $start_time.val(start_date.format('HH:mm')).trigger('change');
    $customer.val(null).trigger('change');
    $internal_note.val(null);
    $notification.val(BookmeBooking.form.notification || 'no');

    prepare_custom_fields();
    check_errors_ajax = true;
}

function setup_edit_form(booking_id) {
    $form_loader.show();
    $form_wrapper.hide();

    jQuery.post(
        ajaxurl,
        {
            action: 'bookme_get_booking_data',
            id: booking_id,
            csrf_token: BookmeBooking.csrf_token
        },
        function (response) {
            if (response.success) {
                $form_loader.hide();
                $form_wrapper.show();

                var start_date = moment(response.data.start_date);

                check_errors_ajax = false;
                $form.data('id', booking_id);
                $staff.val(response.data.staff_id).trigger('change');
                $service.val(response.data.service_id).trigger('change');
                $date.datepicker('setDate', start_date.clone().local().toDate());
                $start_time.val(start_date.format('HH:mm')).trigger('change');
                $internal_note.val(response.data.internal_note);
                $notification.val(BookmeBooking.form.notification || 'no');

                if(response.data.online_meeting_url){
                    $online_meeting_url.val(response.data.online_meeting_url);
                    $online_meeting_url_link.attr('href', response.data.online_meeting_url);
                    $online_meeting_pass.val(response.data.online_meeting_password);
                    $online_meeting_wrapper.show();
                } else {
                    $online_meeting_wrapper.hide();
                }

                prepare_custom_fields();

                $customer.val(null);
                // select customers first for trigger
                response.data.customers.forEach(function (item, i, arr) {
                    $customer.find('option[value="' + item.id + '"]').prop('selected', true);
                });
                $customer.trigger('change');

                // add customer data
                response.data.customers.forEach(function (customer, i, arr) {
                    var item = jQuery('#customer-list-item-' + customer.id);
                    item
                        .data('status', customer.status)
                        .data('number_of_persons', customer.number_of_persons)
                        .data('custom_fields', customer.custom_fields)
                        .data('ca_id', customer.ca_id);

                    item.find('.bm-customer-status').html(jQuery('.bm-status-selector').find('i.' + customer.status).clone());
                    item.find('.bm-customer-nop').text(customer.number_of_persons);
                });
                show_customer_limit();

                check_errors_ajax = true;
            }
        },
        'json'
    );
}

function prepare_custom_fields() {
    if (BookmeBooking.cf_per_service == 1) {
        var show = false;
        jQuery('#bookme-custom-fields-wrapper div[data-services]').each(function () {
            var $this = jQuery(this);
            if ($service.val()) {
                var services = $this.data('services');
                if (services && jQuery.inArray($service.val(), services) > -1) {
                    $this.show();
                    show = true;
                } else {
                    $this.hide();
                }
            } else {
                $this.hide();
            }
        });
        if (show) {
            jQuery('#bookme-custom-fields-wrapper').show();
        } else {
            jQuery('#bookme-custom-fields-wrapper').hide();
        }
    }
}

function check_customer_selector_visibility() {
    if (get_total_not_cancelled_nop() >= $service.find('option:selected').data('capacity_max')) {
        $customer_selector.hide();
    } else {
        $customer_selector.show();
    }
}

function get_total_nop() {
    var result = 0;
    $customers_list.children().each(function () {
        result += parseInt(jQuery(this).data('number_of_persons') || 1);
    });
    return result;
}

function get_total_not_cancelled_nop(except_customer_id) {
    var result = 0;

    $customers_list.children().each(function () {
        if ((!except_customer_id || jQuery(this).data('id') != except_customer_id) && jQuery(this).data('status') != 'cancelled' && jQuery(this).data('status') != 'rejected') {
            result += parseInt(jQuery(this).data('number_of_persons') || 1);
        }
    });

    return result;
}

function show_customer_limit(){
    if($service.val()){
        $customer_limit.text('(' + get_total_nop() + '/' + $service.find('option:selected').data('capacity_max') + ')');
        $customer_limit.show();
    }
}

jQuery(function ($) {
    $date.datepicker({
        dateFormat: BookmeBooking.dateOptions.dateFormat,
        dayNamesMin: BookmeBooking.dateOptions.dayNamesMin,
        monthNames: BookmeBooking.dateOptions.monthNames,
        monthNamesShort: BookmeBooking.dateOptions.monthNamesShort,
        firstDay: BookmeBooking.dateOptions.firstDay
    });

    $customer.select2({
        width: '100%',
        allowClear: false,
        language: {
            noResults: function () {
                return BookmeBooking.no_result_found;
            }
        }
    });

    $staff.on('change', function () {
        $service.html('<option value="">' + BookmeBooking.select_service + '</option>');

        var staff = $(this).val();
        if (staff) {
            var services = BookmeBooking.form.staff[staff].services;
            $.each(services, function (key, val) {
                var $option = $('<option value="' + key + '">' + val.title + '</option>');
                $option.data('duration', val.duration);
                $option.data('capacity_min', val.capacity_min);
                $option.data('capacity_max', val.capacity_max);
                $service.append($option);
            });
            var keys = Object.keys(services);
            if (keys.length == 1) {
                $service.val(services[keys[0]].id).trigger('change');
            } else {
                $service.val(null);
            }
        }
    });

    $service.on('change', function () {
        var id = $(this).val();
        if (id) {
            if ($service.find('option:selected').data('duration') >= 86400) {
                $('#bookme-time').hide();
            } else {
                $('#bookme-time').show();
            }
            show_customer_limit();
        } else {
            $customer_limit.hide();
        }

        check_customer_selector_visibility();

        prepare_custom_fields();
        check_booking_errors();
    });

    $date.on('change', function () {
        check_booking_errors();
    });

    $start_time.on('change', function () {
        check_booking_errors();
    });

    $customer.on('change', function () {
        var ids = $(this).val() || [],
            old_ids = [];

        // get old customer ids
        $.each(current_customers_list, function (i, id) {
            if ($.inArray(id, ids) === -1) {
                old_ids.push(id);
            }
        });

        // remove old ids
        $.each(old_ids, function (i, id) {
            $('#customer-list-item-' + id).remove();
            current_customers_list.splice($.inArray(id, current_customers_list), 1);
        });

        // add newly added customers
        $.each(ids, function (i, id) {
            if ($.inArray(id, current_customers_list) === -1) {
                var customer = BookmeBooking.form.customers[id];
                var $template = $($('#bm-customer-template').html());
                $template.attr('id', 'customer-list-item-' + id)
                    .data('id', id)
                    .data('status', customer.status)
                    .data('number_of_persons', customer.number_of_persons)
                    .data('custom_fields', customer.custom_fields);

                // edit customer details
                $template.find('.bm-customer-name').html(customer.name)
                    .on('click', function () {
                        var $panel = $('#bm-customer-details-panel');
                        $panel.find('input.bookme-custom-field:text, textarea.bookme-custom-field, select.bookme-custom-field').val('');
                        $panel.find('input.bookme-custom-field:checkbox, input.bookme-custom-field:radio').prop('checked', false);

                        $template.data('custom_fields').forEach(function (field) {
                            // fill custom fields for booking
                            var $custom_field = $panel.find('#bookme-custom-fields-wrapper > *[data-id="' + field.id + '"]');
                            switch ($custom_field.data('type')) {
                                case 'checkboxes':
                                    field.value.forEach(function (value) {
                                        $custom_field.find('.bookme-custom-field').filter(function () {
                                            return this.value == value;
                                        }).prop('checked', true);
                                    });
                                    break;
                                case 'radio-buttons':
                                    $custom_field.find('.bookme-custom-field').filter(function () {
                                        return this.value == field.value;
                                    }).prop('checked', true);
                                    break;
                                default:
                                    $custom_field.find('.bookme-custom-field').val(field.value);
                                    break;
                            }
                        });

                        // Prepare select for number of persons.
                        var $number_of_persons = $panel.find('#bookme-nop-field'),
                            nop = $template.data('number_of_persons');

                        var max = $service.val()
                            ? parseInt($service.find('option:selected').data('capacity_max')) - get_total_not_cancelled_nop(id)
                            : 1;
                        $number_of_persons.empty();
                        for (var i = 1; i <= max; ++i) {
                            $number_of_persons.append('<option value="' + i + '">' + i + '</option>');
                        }
                        if (nop > max) {
                            $number_of_persons.append('<option value="' + nop + '">' + nop + '</option>');
                        }
                        $number_of_persons.val(nop);
                        $panel.find('#bookme-booking-status').val($template.data('status'));

                        $panel.data('id', id);
                        bm_show_sidepanel($panel);

                        $panel.on('sidePanel.hide', function () {
                            $('body').css('overflow-y', 'hidden');
                        });
                    });

                // on status change
                $template.find('.bm-status-selector > li').on('click', function () {
                    $template.find('.bm-customer-status').html($(this).find('i').clone());
                    $template.data('status', $(this).data('status'));
                    check_customer_selector_visibility();
                });

                // on click on remove customer
                $template.find('.bm-remove-customer').on('click', function () {
                    $customer.find("option[value='" + id + "']").prop("selected", false);
                    $customer.trigger('change');
                });

                $customers_list.append($template);
                current_customers_list.push(id);
            }
        });

        show_customer_limit();
        check_customer_selector_visibility();
        check_booking_errors();
    });

    // save customers
    $('.bm-new-customer').on('click', function () {
        show_customer_dialog(null, function (customer) {

            // Add new customer to the list.
            var nop = 1;
            if ($service.val()) {
                nop = $service.find('option:selected').data('capacity_min') - get_total_not_cancelled_nop();
                if (nop < 1) {
                    nop = 1;
                }
            }
            var new_customer = {
                id: customer.id.toString(),
                name: customer.full_name,
                custom_fields: [],
                status: BookmeBooking.form.status.default,
                number_of_persons: nop,
                payment_id: null,
                payment_type: null,
                payment_title: null
            };

            if (customer.email || customer.phone) {
                new_customer.name += ' (' + [customer.email, customer.phone].filter(Boolean).join(', ') + ')';
            }

            BookmeBooking.form.customers[new_customer.id] = new_customer;
            $customer.append('<option value="' + new_customer.id + '">' + new_customer.name + '</option>');

            // Make it selected.
            if (!$service.val() || get_total_nop() < $service.find('option:selected').data('capacity_max')) {
                $customer.find('option[value="' + new_customer.id + '"]').prop('selected', true);
                $customer.trigger('change');
            }
        });
    });

    // save customer details
    $('.bm-save-customer-details').on('click', function () {
        var result = [],
            $fields = $('#bookme-custom-fields-wrapper > *');

        $fields.each(function () {
            var $this = $(this),
                value;
            if ($this.is(':visible')) {
                switch ($this.data('type')) {
                    case 'checkboxes':
                        value = [];
                        $this.find('.bookme-custom-field:checked').each(function () {
                            value.push(this.value);
                        });
                        break;
                    case 'radio-buttons':
                        value = $this.find('.bookme-custom-field:checked').val();
                        break;
                    default:
                        value = $this.find('.bookme-custom-field').val();
                        break;
                }
                result.push({id: $this.data('id'), value: value});
            }
        });

        var $panel = $('#bm-customer-details-panel'),
            $template = $('#customer-list-item-' + $panel.data('id')),
            number_of_persons = $('#bm-customer-details-panel #bookme-nop-field').val(),
            status = $('#bm-customer-details-panel #bookme-booking-status').val();

        $template.data('status', status)
            .data('number_of_persons', number_of_persons)
            .data('custom_fields', result);

        $template.find('.bm-customer-status').html($('.bm-status-selector').find('i.' + status).clone());
        $template.find('.bm-customer-nop').text(number_of_persons);
        check_customer_selector_visibility();

        $(this).data('id', null);
        bm_hide_sidepanel($panel);
    });

    // save bookings
    $('.ajax-save-booking').on('click', function () {
        var $this = $(this);
        if (validateForm($form)) {
            var customers = get_customers(),
                dates = get_booking_time();

            $errors.slideUp();
            $this.addClass('bookme-loader').prop('disabled', true);
            $.post(
                ajaxurl,
                {
                    action: 'bookme_save_booking',
                    csrf_token: BookmeBooking.csrf_token,
                    id: $form.data('id'),
                    staff_id: $staff.val(),
                    service_id: $service.val(),
                    start_date: dates.start_date,
                    end_date: dates.end_date,
                    customers: JSON.stringify(customers),
                    notification: $notification.val(),
                    internal_note: $internal_note.val()
                },
                function (response) {
                    if(response.alert && response.alert.length){
                        bm_alert(response.alert.join('<br>'), 'error');
                    }
                    if (response.success) {
                        if (callback) {
                            // Call callback.
                            callback(response.data);
                        }
                        // Close the panel
                        bm_hide_sidepanel($booking_panel);
                    } else {
                        $.each(response.errors, function (id, value) {
                            $('#bm-error-' + id).html(value).slideDown();
                        });
                    }
                    $this.removeClass('bookme-loader').prop('disabled', false);
                },
                'json'
            );
        }
    });

    function check_booking_errors() {
        $errors.slideUp();
        if (check_errors_ajax) {
            if ($staff.val()) {
                var dates = get_booking_time(),
                    customers = get_customers();

                jQuery.post(
                    ajaxurl,
                    {
                        action: 'bookme_check_booking_errors',
                        csrf_token: BookmeBooking.csrf_token,
                        id: $form.data('id'),
                        staff_id: $staff.val(),
                        service_id: $service.val() ? $service.val() : null,
                        start_date: dates.start_date,
                        end_date: dates.end_date,
                        customers: JSON.stringify(customers)
                    },
                    function (response) {
                        $.each(response, function (id, value) {
                            $('#bm-error-' + id).html(value).slideDown();
                        });
                    },
                    'json'
                );
            }
        }
    }

    function get_booking_time() {
        var start_date = moment($date.datepicker( 'getDate' )),
            end_date = moment($date.datepicker( 'getDate' )),
            start_val = null,
            end_val = null,
            service_duration = $service.val()
                ? $service.find('option:selected').data('duration')
                : null;

        if (service_duration >= 86400) {
            end_date.add(service_duration, 'seconds');

            start_date.hours(0);
            start_date.minutes(0);
            end_date.hours(0);
            end_date.minutes(0);

            start_val = start_date.format('YYYY-MM-DD HH:mm:00');
            end_val = end_date.format('YYYY-MM-DD HH:mm:00');
        } else {
            var start_time = $start_time.val()? $start_time.val().split(':'): [0,0];
            start_date.hours(start_time[0]);
            start_date.minutes(start_time[1]);
            start_val = start_date.format('YYYY-MM-DD HH:mm:00');

            end_date = start_date;
            end_date.add(service_duration, 'seconds');
            end_val = end_date.format('YYYY-MM-DD HH:mm:00');
        }


        return {
            start_date: start_val,
            end_date: end_val
        };
    }

    function get_customers() {
        var customers = [];
        $customers_list.children().each(function () {
            customers.push({
                id: $(this).data('id'),
                ca_id: $(this).data('ca_id'),
                custom_fields: $(this).data('custom_fields'),
                number_of_persons: $(this).data('number_of_persons'),
                status: $(this).data('status')
            });
        });
        return customers;
    }
});