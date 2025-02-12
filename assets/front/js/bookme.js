(function ($) {
    window.bookme = function (form_id, attributes, skip_steps, booking_status) {
        var $booking_wrapper = $('#bookme-booking-form-' + form_id),
            timeZone = typeof Intl === 'object' ? Intl.DateTimeFormat().resolvedOptions().timeZone : undefined,
            timeZoneOffset = new Date().getTimezoneOffset();

        if (Bookme.stripe.enabled) {
            // insert stripe js if enabled
            var stripe_script = document.createElement("script");
            stripe_script.type = "text\/javascript";
            stripe_script.async = true;
            document.head.appendChild(stripe_script);
            stripe_script.src = "https://js.stripe.com/v3/";
        }

        if (Bookme.razorpay.enabled) {
            // insert razorpay js if enabled
            var razorpay_script = document.createElement("script");
            razorpay_script.type = "text\/javascript";
            razorpay_script.async = true;
            document.head.appendChild(razorpay_script);
            razorpay_script.src = "https://checkout.razorpay.com/v1/checkout.js";
        }

        if (booking_status.status == 'finished') {
            render_done_step();
        } else if (booking_status.status == 'cancelled') {
            render_detail_step();
        } else {
            render_service_step({reset_sequence: true});
        }

        function render_service_step(_data) {

            var data = $.extend({
                action: 'bookme_get_service_step',
                csrf_token: Bookme.csrf_token,
                form_id: form_id,
                time_zone: timeZone,
                time_zone_offset: timeZoneOffset
            }, _data);

            $.ajax({
                url: Bookme.ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    if (response.success) {
                        Bookme.csrf_token = response.csrf_token;
                        $booking_wrapper.html(response.html);

                        if (_data === undefined) {
                            scrollTo($booking_wrapper);
                        }

                        var $category_field = $('.bookme-category', $booking_wrapper),
                            $service_field = $('.bookme-service', $booking_wrapper),
                            $employee_field = $('.bookme-employee', $booking_wrapper),
                            $nop_field = $('.bookme-number-of-persons', $booking_wrapper),
                            $calendar = $('.bookme-calendar', $booking_wrapper),
                            $date = $('.bookme-date', $booking_wrapper),
                            $next_button = $('.bookme-next', $booking_wrapper),
                            categories = response.categories,
                            services = response.services,
                            staff = response.staff,
                            data = response.data,
                            category_selected = false;

                        // init calendar
                        $calendar.clndr({
                            selectedDate: $date.val(),
                            monthsLabel: Bookme.months,
                            daysOfTheWeek: Bookme.daysShort,
                            weekOffset: Bookme.start_of_week,
                            constraints: {
                                startDate: response.date_min,
                                endDate: response.date_max
                            },
                            multiDayEvents: {
                                startDate: 'startDate',
                                endDate: 'endDate'
                            },
                            showAdjacentMonths: true,
                            adjacentDaysChangeMonth: false,
                            clickEvents: {
                                click: function (target) {
                                    var $day = $(target.element);
                                    if (!$day.hasClass('inactive') && !$day.hasClass("past") && !$day.hasClass("adjacent-month")) {
                                        $calendar.find(".day").removeClass('selected');
                                        $day.addClass('selected');
                                        $date.val($day.data('date'));
                                    }
                                },
                                nextMonth: get_availability,
                                previousMonth: get_availability
                            }
                        });

                        $booking_wrapper.off('click').off('change');

                        $booking_wrapper.on('change', '.bookme-category', function () {
                            var category_id = this.value,
                                service_id = $service_field.val(),
                                staff_id = $employee_field.val();

                            if (category_id) {
                                category_selected = true;
                                if (service_id) {
                                    if (services[service_id].category_id != category_id) {
                                        service_id = '';
                                    }
                                }
                                if (staff_id) {
                                    var valid = false;
                                    $.each(staff[staff_id].services, function (id) {
                                        if (services[id].category_id == category_id) {
                                            valid = true;
                                            return false;
                                        }
                                    });
                                    if (!valid) {
                                        staff_id = '';
                                    }
                                }
                            } else {
                                category_selected = false;
                            }
                            set_selects(category_id, service_id, staff_id);
                        });

                        $booking_wrapper.on('change', '.bookme-service', function () {
                            var category_id = category_selected ? $category_field.val() : '',
                                service_id = this.value,
                                staff_id = $employee_field.val();

                            if (service_id) {
                                if (staff_id && !staff[staff_id].services.hasOwnProperty(service_id)) {
                                    staff_id = '';
                                }
                            }
                            set_selects(category_id, service_id, staff_id);
                            if (service_id) {
                                $category_field.val(services[service_id].category_id);
                                get_availability();
                            }
                        });

                        $booking_wrapper.on('change', '.bookme-employee', function () {
                            var category_id = $category_field.val(),
                                service_id = $service_field.val(),
                                staff_id = this.value;

                            set_selects(category_id, service_id, staff_id);
                            get_availability();
                        });

                        $('.bookme-cart', $booking_wrapper).on('click', function (e) {
                            e.preventDefault();
                            $(this).addClass('bookme-loader').prop('disabled',true);
                            render_cart_step({from_step: 'service'});
                        });

                        $next_button.on('click', function (e) {
                            e.preventDefault();
                            var $this = $(this);
                            if (validate_service_step()) {
                                var staff_ids = [];
                                if ($employee_field.val()) {
                                    staff_ids.push($employee_field.val());
                                } else {
                                    $employee_field.find('option').each(function () {
                                        if (this.value) {
                                            staff_ids.push(this.value);
                                        }
                                    });
                                }

                                var sequence = {
                                    0 : {
                                        service_id: $service_field.val(),
                                        staff_ids: staff_ids,
                                        number_of_persons: $nop_field.val()
                                    }
                                };

                                $this.addClass('bookme-loader').prop('disabled',true);
                                render_time_step({sequence: sequence, date: $date.val()});
                            }
                        });

                        if (attributes.show_service_duration) {
                            $.each(services, function (id, service) {
                                service.name = service.name + ' ( ' + service.duration + ' )';
                            });
                        }

                        set_select($category_field, categories);
                        set_select($service_field, services);
                        set_select($employee_field, staff);
                        $category_field.closest('.bookme-form-group').toggle(!attributes.hide_categories);
                        $service_field.closest('.bookme-form-group').toggle(!(attributes.hide_services && attributes.service_id));
                        $employee_field.closest('.bookme-form-group').toggle(!attributes.hide_staff_members);
                        $nop_field.closest('.bookme-form-group').toggle(attributes.show_number_of_persons);
                        $('.bookme-service-step-left', $booking_wrapper).toggle(!skip_steps.service_left);
                        if(skip_steps.service_left){
                            $('.bookme-service-step-right', $booking_wrapper).removeClass('bookme-col-md-6').addClass('bookme-col-md-12');
                        }

                        if (attributes.category_id) {
                            $category_field.val(attributes.category_id).trigger('change');
                        }
                        if (attributes.service_id) {
                            $service_field.val(attributes.service_id).trigger('change');
                        }
                        if (attributes.staff_member_id) {
                            $employee_field.val(attributes.staff_member_id).trigger('change');
                        }

                        if(data.service_id){
                            $category_field.val(services[data.service_id].category_id).trigger('change');
                            $service_field.val(data.service_id).trigger('change');
                            if (attributes.hide_categories) {
                                $category_field.val('');
                            }
                        }
                        if (!attributes.hide_staff_members && data.staff_ids.length == 1 && data.staff_ids[0]) {
                            $employee_field.val(data.staff_ids[0]).trigger('change');
                        }
                        if (data.number_of_persons > 1) {
                            $nop_field.val(data.number_of_persons);
                        }

                        function validate_service_step() {
                            $('.bookme-service-error', $booking_wrapper).hide();
                            $('.bookme-employee-error', $booking_wrapper).hide();

                            var valid = true,
                                $scroll_to = null;

                                $service_field.removeClass('bookme-error');
                                $employee_field.removeClass('bookme-error');

                                // service validation
                                if (!$service_field.val()) {
                                    valid = false;
                                    $service_field.addClass('bookme-error');
                                    $('.bookme-service-error', $booking_wrapper).show();
                                    $scroll_to = $service_field;
                                }
                                if (Bookme.required.staff && !$employee_field.val()) {
                                    valid = false;
                                    $employee_field.addClass('bookme-error');
                                    $('.bookme-employee-error', $booking_wrapper).show();
                                    $scroll_to = $employee_field;
                                }


                            if ($scroll_to !== null) {
                                scrollTo($scroll_to);
                            }

                            return valid;
                        }

                        function get_availability() {
                            if (!$service_field.val())
                                return;

                            $calendar.css('opacity', 0.5).addClass('bookme-loader');

                            var staff_ids = [];
                            if ($employee_field.val()) {
                                staff_ids.push($employee_field.val());
                            } else {
                                $employee_field.find('option').each(function () {
                                    if (this.value) {
                                        staff_ids.push(this.value);
                                    }
                                });
                            }

                            var sequence = {
                                0 : {
                                    service_id: $service_field.val(),
                                    staff_ids: staff_ids,
                                    number_of_persons: $nop_field.val()
                                }
                            };

                            $.ajax({
                                type: 'POST',
                                url: Bookme.ajaxurl,
                                data: {
                                    action: 'bookme_get_availability',
                                    csrf_token: Bookme.csrf_token,
                                    form_id: form_id,
                                    sequence: sequence,
                                    date: $calendar.data('date')
                                },
                                dataType: 'json',
                                xhrFields: {withCredentials: true},
                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    if (response.success) {
                                        Bookme.csrf_token = response.csrf_token;
                                        var slots = response.availability;
                                        //initTooltip();
                                        $('.bookme-calendar').find('.day').each(function () {
                                            if (!$(this).hasClass("past") && !$(this).hasClass("adjacent-month")) {
                                                var caldate = $(this).data('date');
                                                if (slots[caldate] != undefined) {
                                                    $(this).attr("title", slots[caldate]);
                                                    $(this).removeClass('bookme-hide');
                                                } else {
                                                    $(this).attr("title", Bookme.not_available);
                                                    $(this).addClass('bookme-hide');
                                                }
                                                $(this).bookme_tooltip();
                                            }
                                        });
                                    }
                                    $calendar.css('opacity', 1).removeClass('bookme-loader');
                                }
                            });

                        }

                        function set_select($select, data, value) {
                            // reset select
                            $('option:not([value=""])', $select).remove();
                            // and fill the new data
                            var docFragment = document.createDocumentFragment();

                            function valuesToArray(obj) {
                                return Object.keys(obj).map(function (key) {
                                    return obj[key];
                                });
                            }

                            function compare(a, b) {
                                if (parseInt(a.position) < parseInt(b.position))
                                    return -1;
                                if (parseInt(a.position) > parseInt(b.position))
                                    return 1;
                                return 0;
                            }

                            // sort select by position
                            data = valuesToArray(data).sort(compare);

                            $.each(data, function (key, object) {
                                var option = document.createElement('option');
                                option.value = object.id;
                                option.text = object.name;
                                docFragment.appendChild(option);
                            });
                            $select.append(docFragment);
                            // set default value of select
                            $select.val(value);
                        }

                        function set_selects(category_id, service_id, staff_id) {
                            var _staff = {}, _services = {}, _categories = {}, _nop = {};
                            $.each(staff, function (id, staff_member) {
                                if (!service_id) {
                                    if (!category_id) {
                                        _staff[id] = staff_member;
                                    } else {
                                        $.each(staff_member.services, function (s_id) {
                                            if (services[s_id].category_id == category_id) {
                                                _staff[id] = staff_member;
                                                return false;
                                            }
                                        });
                                    }
                                } else if (staff_member.services.hasOwnProperty(service_id)) {
                                    if (staff_member.services[service_id].price != null) {
                                        _staff[id] = {
                                            id: id,
                                            name: staff_member.name + ' (' + staff_member.services[service_id].price + ')',
                                            position: staff_member.position
                                        };
                                    } else {
                                        _staff[id] = staff_member;
                                    }
                                }
                            });

                            _categories = categories;
                            $.each(services, function (id, service) {
                                if (!category_id || service.category_id == category_id) {
                                    if (!staff_id || staff[staff_id].services.hasOwnProperty(id)) {
                                        _services[id] = service;
                                    }
                                }
                            });

                            var nop = $nop_field.val();
                            var max_capacity = service_id
                                ? (staff_id
                                    ? staff[staff_id].services[service_id].max_capacity
                                    : services[service_id].max_capacity)
                                : 1;
                            var min_capacity = service_id
                                ? (staff_id
                                    ? staff[staff_id].services[service_id].min_capacity
                                    : services[service_id].min_capacity)
                                : 1;
                            for (var i = min_capacity; i <= max_capacity; ++i) {
                                _nop[i] = {id: i, name: i, pos: i};
                            }
                            if (nop > max_capacity) {
                                nop = max_capacity;
                            }
                            if (nop < min_capacity || !attributes.show_number_of_persons) {
                                nop = min_capacity;
                            }
                            set_select($category_field, _categories, category_id);
                            set_select($service_field, _services, service_id);
                            set_select($employee_field, _staff, staff_id);
                            set_select($nop_field, _nop, nop);
                        }
                    }
                }
            });
        }

        function render_time_step(_data, error, _failed_callback){

            var data = $.extend({
                action: 'bookme_get_time_step',
                csrf_token: Bookme.csrf_token,
                form_id: form_id
            }, _data);

            $.ajax({
                url: Bookme.ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    if (response.success == false) {
                        if(_failed_callback){
                            _failed_callback(response);
                        }
                        return;
                    }
                    Bookme.csrf_token = response.csrf_token;
                    $booking_wrapper.html(response.html);

                    if (error) {
                        $booking_wrapper.find('.bookme-form-error').html(error);
                    } else {
                        $booking_wrapper.find('.bookme-form-error').hide();
                    }

                    // scroll content
                    $('.bookme-timeslot-scroll').TrackpadScrollEmulator();

                    scrollTo($booking_wrapper);

                    $('.bookme-back', $booking_wrapper).on('click', function (e) {
                        e.preventDefault();
                        $(this).addClass('bookme-loader').prop('disabled',true);
                        render_service_step();
                    });

                    $('.bookme-cart', $booking_wrapper).on('click', function (e) {
                        e.preventDefault();
                        $(this).addClass('bookme-loader').prop('disabled',true);
                        render_cart_step({from_step: 'time'});
                    });

                    $('.bookme-timeslot-button', $booking_wrapper).on('click', function (e) {
                        e.preventDefault();
                        var $this = $(this);

                        $this.addClass('bookme-loader').prop('disabled',true);
                        if (Bookme.cart.enabled) {
                            render_cart_step({add_to_cart: true, from_step: 'time', slots: this.value});
                        } else {
                            render_detail_step({add_to_cart: true, slots: this.value});
                        }
                    });
                }
            });

        }

        function render_cart_step(_data, error, _failed_callback) {
            if (!Bookme.cart.enabled) {
                render_detail_step(_data);
            } else {
                if (_data && _data.from_step) {
                    Bookme.cart.prev_step = _data.from_step;
                }

                var data = $.extend({
                    action: 'bookme_get_cart_step',
                    csrf_token: Bookme.csrf_token,
                    form_id: form_id
                }, _data);

                $.ajax({
                    url: Bookme.ajaxurl,
                    data: data,
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {
                        if (response.success) {
                            Bookme.csrf_token = response.csrf_token;

                            $booking_wrapper.html(response.html);

                            if (error) {
                                $('.bookme-form-error', $booking_wrapper).html(error.message);
                                $('tr[data-key="' + error.failed_key + '"]', $booking_wrapper).addClass('bookme-form-error');
                            } else {
                                $('.bookme-form-error', $booking_wrapper).hide();
                            }
                            scrollTo($booking_wrapper);

                            $('.bookme-cart-add', $booking_wrapper).on('click', function () {
                                $(this).addClass('bookme-loader').prop('disabled',true);
                                render_service_step({reset_sequence: true});
                            });

                            $('.bookme-cart-edit', $booking_wrapper).on('click', function () {
                                var $this = $(this);
                                $this.addClass('bookme-loader').prop('disabled',true);
                                render_service_step({edit_cart_item: $this.closest('tr').data('key')});
                            });
                            $('.bookme-cart-delete', $booking_wrapper).on('click', function () {
                                var $this = $(this),
                                    $cart_item = $this.closest('tr');

                                $this.addClass('bookme-loader').prop('disabled',true);
                                $.ajax({
                                    url: Bookme.ajaxurl,
                                    data: {
                                        action: 'bookme_cart_delete_item',
                                        csrf_token: Bookme.csrf_token,
                                        form_id: form_id,
                                        key: $cart_item.data('key')
                                    },
                                    dataType: 'json',
                                    xhrFields: {withCredentials: true},
                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                    success: function (response) {
                                        if (response.success) {
                                            var $trs_to_remove = $('tr[data-key="' + $cart_item.data('cart-key') + '"]', $booking_wrapper);

                                            $cart_item.delay(300).fadeOut(200, function () {
                                                $('.bookme-cart-total', $booking_wrapper).html(response.data.total_price);
                                                $trs_to_remove.remove();
                                                if ($('tr[data-key]', $booking_wrapper).length == 0) {
                                                    $('.bookme-back', $booking_wrapper).hide();
                                                    $('.bookme-next', $booking_wrapper).hide();
                                                }
                                            });
                                        }
                                    }
                                });
                            });

                            $('.bookme-back', $booking_wrapper).on('click', function (e) {
                                e.preventDefault();
                                $(this).addClass('bookme-loader').prop('disabled',true);
                                switch (Bookme.cart.prev_step) {
                                    case 'service':
                                        render_service_step();
                                        break;
                                    case 'time':
                                        render_time_step();
                                        break;
                                    default:
                                        render_service_step();
                                }
                            });

                            $('.bookme-next', $booking_wrapper).on('click', function () {
                                $(this).addClass('bookme-loader').prop('disabled',true);
                                render_detail_step();
                            });
                        }
                    }
                });
            }
        }

        function render_detail_step(_data, _failed_callback){
            var data = $.extend({
                action: 'bookme_get_detail_step',
                csrf_token: Bookme.csrf_token,
                form_id: form_id,
                page_url: document.URL.split('#')[0]
            }, _data);

            $.ajax({
                url: Bookme.ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    if (response.success) {
                        Bookme.csrf_token = response.csrf_token;

                        $booking_wrapper.html(response.html);
                        scrollTo($booking_wrapper);

                        // Init stripe form
                        if ($booking_wrapper.find('#bookme-stripe-card-element').length) {
                            if (response.stripe_publishable_key) {
                                var stripe = Stripe(response.stripe_publishable_key, {
                                    betas: ['payment_intent_beta_3']
                                });
                                var elements = stripe.elements();
                                var stripe_card = elements.create("card"/*, { hidePostalCode: true}*/);
                                stripe_card.mount("#bookme-stripe-card-element");
                            }
                        }

                        var $full_name_field = $('.bookme-full-name', $booking_wrapper),
                            $first_name_field = $('.bookme-first-name', $booking_wrapper),
                            $last_name_field = $('.bookme-last-name', $booking_wrapper),
                            $phone_field = $('.bookme-phone', $booking_wrapper),
                            $email_field = $('.bookme-email', $booking_wrapper),
                            $g_recaptcha = $('.bookme-g-recaptcha', $booking_wrapper),
                            $full_name_error = $('.bookme-full-name-error', $booking_wrapper),
                            $first_name_error = $('.bookme-first-name-error', $booking_wrapper),
                            $last_name_error = $('.bookme-last-name-error', $booking_wrapper),
                            $phone_error = $('.bookme-phone-error', $booking_wrapper),
                            $email_error = $('.bookme-email-error', $booking_wrapper),
                            $g_recaptcha_error = $('.bookme-recaptcha-error', $booking_wrapper),
                            $errors = $('.bookme-full-name-error, .bookme-first-name-error, .bookme-last-name-error, .bookme-phone-error, .bookme-email-error, .bookme-recaptcha-error, .bookme-custom-field-error', $booking_wrapper),
                            $fields = $('.bookme-full-name, .bookme-first-name, .bookme-last-name, .bookme-phone, .bookme-email, .bookme-g-recaptcha, .bookme-custom-field', $booking_wrapper),
                            $modals = $('.bookme-modal', $booking_wrapper),
                            $login_modal = $('.bookme-login-modal', $booking_wrapper),
                            $customer_modal = $('.bookme-customer-modal', $booking_wrapper),
                            $payments = $('.bookme-payment', $booking_wrapper),
                            $payment_tabs = $('.bookme-pay-tab', $booking_wrapper),
                            $apply_coupon_button = $('.bookme-apply-coupon', $booking_wrapper),
                            $coupon_input = $('input.bookme-coupon-field', $booking_wrapper),
                            $coupon_discount = $('.bookme-discount-price', $booking_wrapper),
                            $coupon_total = $('.bookme-total-price', $booking_wrapper),
                            $coupon_error = $('.bookme-coupon-error', $booking_wrapper),
                            $bookme_payment_wrapper = $('.bookme-payment-wrapper', $booking_wrapper),
                            phone_number = '',
                            g_recaptcha_response = '',
                            g_recaptcha_id = null;

                        if (booking_status.status == 'cancelled') {
                            booking_status.status = 'ok';
                        }

                        if (Bookme.intlTelInput.enabled) {
                            $phone_field.intlTelInput({
                                preferredCountries: [Bookme.intlTelInput.country],
                                initialCountry: Bookme.intlTelInput.country,
                                geoIpLookup: function (callback) {
                                    $.get('https://ipinfo.io', function () {
                                    }, 'jsonp').always(function (resp) {
                                        var countryCode = (resp && resp.country) ? resp.country : '';
                                        callback(countryCode);
                                    });
                                },
                                utilsScript: Bookme.intlTelInput.utils
                            });
                        }

                        if($g_recaptcha.length && Bookme.captcha_site_key) {
                            var recaptcha = {
                                sitekey: Bookme.captcha_site_key
                            };
                            g_recaptcha_id = grecaptcha.render($g_recaptcha.get(0), recaptcha);
                        }

                        $payments.on('click', function () {
                            $payment_tabs.slideUp();
                            $('.bookme-' + $(this).data('tab'), $booking_wrapper).slideDown();
                        });
                        $payments.eq(0).trigger('click');

                        $('body > .bookme-modal.' + form_id).remove();
                        $modals
                            .addClass(form_id).appendTo('body')
                            .on('click', '.bookme-modal-dismiss', function (e) {
                                e.preventDefault();
                                $(e.delegateTarget).removeClass('bookme-modal-show')
                                    .find('form').trigger('reset').end()
                                    .find('input').removeClass('bookme-error').end()
                                    .find('.bookme-form-error').html('');
                            });

                        // Login modal
                        $('.bookme-login-dialog-show', $booking_wrapper).on('click', function (e) {
                            e.preventDefault();
                            $login_modal.addClass('bookme-modal-show');
                        });

                        $('form', $login_modal).on('submit', function (e) {
                            e.preventDefault();
                            var $button = $(this).find('.bookme-modal-submit');
                            $button.addClass('bookme-loader').prop('disabled',true);
                            $.ajax({
                                type: 'POST',
                                url: Bookme.ajaxurl,
                                data: {
                                    action: 'bookme_wp_user_login',
                                    csrf_token: Bookme.csrf_token,
                                    form_id: form_id,
                                    log: $login_modal.find('[name="log"]').val(),
                                    pwd: $login_modal.find('[name="pwd"]').val(),
                                    rememberme: $login_modal.find('[name="rememberme"]').prop('checked') ? 1 : 0
                                },
                                dataType: 'json',
                                xhrFields: {withCredentials: true},
                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    if (response.success) {
                                        Bookme.csrf_token = response.data.csrf_token;
                                        $('.bookme-login-dialog-show', $booking_wrapper).parent().fadeOut('slow');
                                        $full_name_field.val(response.data.full_name).removeClass('bookme-error');
                                        $first_name_field.val(response.data.first_name).removeClass('bookme-error');
                                        $last_name_field.val(response.data.last_name).removeClass('bookme-error');
                                        if (response.data.phone) {
                                            $phone_field.removeClass('bookme-error');
                                            if (Bookme.intlTelInput.enabled) {
                                                $phone_field.intlTelInput('setNumber', response.data.phone);
                                            } else {
                                                $phone_field.val(response.data.phone);
                                            }
                                        }
                                        $email_field.val(response.data.email).removeClass('bookme-error');
                                        $errors.filter(':not(.bookme-custom-field-error)').html('');
                                        $login_modal.removeClass('bookme-modal-show');
                                    } else if (response.error) {
                                        $login_modal.find('input').addClass('bookme-error');
                                        $login_modal.find('.bookme-form-error').html(response.error);
                                    }
                                    $button.removeClass('bookme-loader').prop('disabled',false);
                                }
                            })
                        });

                        // Customer modal
                        $('.bookme-modal-submit', $customer_modal).on('click', function (e) {
                            e.preventDefault();
                            $customer_modal.removeClass('bookme-modal-show');
                            $('.bookme-next', $booking_wrapper).trigger('click', [1]);
                        });

                        $apply_coupon_button.on('click', function (e) {
                            var $this = $(this);
                            $coupon_error.text('');
                            $coupon_input.removeClass('bookme-error');

                            var data = {
                                action: 'bookme_apply_coupon',
                                csrf_token: Bookme.csrf_token,
                                form_id: form_id,
                                coupon: $coupon_input.val()
                            };

                            $this.addClass('bookme-loader').prop('disabled',true);

                            $.ajax({
                                type: 'POST',
                                url: Bookme.ajaxurl,
                                data: data,
                                dataType: 'json',
                                xhrFields: {withCredentials: true},
                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    if (response.success) {
                                        $coupon_input.parent().removeClass('bookme-d-flex');
                                        $coupon_input.replaceWith(data.coupon);
                                        $apply_coupon_button.replaceWith(' <strong>✓</strong>');
                                        $coupon_discount.text(response.discount);
                                        $coupon_total.text(response.total);
                                        if (response.total_simple <= 0) {
                                            $bookme_payment_wrapper.hide();
                                            //$('.bookme-coupon-free', $booking_wrapper).attr('checked', 'checked').val(data.coupon);
                                            $('.bookme-coupon-free', $booking_wrapper).prop("checked", true).val(data.coupon);
                                        } else {
                                            // Set new price for hidden payment fields
                                            $('input.bookme-payment-amount', $booking_wrapper).val(response.total_simple);
                                        }
                                    } else if (response.error) {
                                        $coupon_error.html(response.error);
                                        $coupon_input.addClass('bookme-error');
                                        scrollTo($coupon_error);
                                    }
                                    $this.removeClass('bookme-loader').prop('disabled',false);
                                },
                                error: function () {
                                    $this.removeClass('bookme-loader').prop('disabled',false);
                                }
                            });
                        });

                        $('.bookme-next', $booking_wrapper).on('click', function (e, force_update_customer) {
                            e.preventDefault();
                            var custom_fields = [],
                                checkbox_values,
                                $this = $(this);

                            $('.bookme-custom-fields-wrapper', $booking_wrapper).each(function () {
                                var $cf_container = $(this),
                                    key = $cf_container.data('key'),
                                    custom_fields_data = [];
                                $('.bookme-form-group', $cf_container).each(function () {
                                    var $this = $(this);
                                    switch ($this.data('type')) {
                                        case 'text-field':
                                            custom_fields_data.push({
                                                id: $this.data('id'),
                                                value: $this.find('input.bookme-custom-field').val()
                                            });
                                            break;
                                        case 'textarea':
                                            custom_fields_data.push({
                                                id: $this.data('id'),
                                                value: $this.find('textarea.bookme-custom-field').val()
                                            });
                                            break;
                                        case 'checkboxes':
                                            checkbox_values = [];
                                            $this.find('input.bookme-custom-field:checked').each(function () {
                                                checkbox_values.push(this.value);
                                            });
                                            custom_fields_data.push({
                                                id: $this.data('id'),
                                                value: checkbox_values
                                            });
                                            break;
                                        case 'radio-buttons':
                                            custom_fields_data.push({
                                                id: $this.data('id'),
                                                value: $this.find('input.bookme-custom-field:checked').val() || null
                                            });
                                            break;
                                        case 'drop-down':
                                            custom_fields_data.push({
                                                id: $this.data('id'),
                                                value: $this.find('select.bookme-custom-field').val()
                                            });
                                            break;
                                    }
                                });
                                custom_fields[key] = {custom_fields: JSON.stringify(custom_fields_data)};
                            });

                            try {
                                phone_number = Bookme.intlTelInput.enabled ? $phone_field.intlTelInput('getNumber') : $phone_field.val();
                                if (phone_number == '') {
                                    phone_number = $phone_field.val();
                                }
                            } catch (error) {  // In case when intlTelInput can't return phone number.
                                phone_number = $phone_field.val();
                            }

                            // Google recaptcha response
                            g_recaptcha_response = g_recaptcha_id != null
                                ? grecaptcha.getResponse(g_recaptcha_id)
                                : null;

                            var data = {
                                action: 'bookme_save_session',
                                csrf_token: Bookme.csrf_token,
                                form_id: form_id,
                                full_name: $full_name_field.val(),
                                first_name: $first_name_field.val(),
                                last_name: $last_name_field.val(),
                                phone: phone_number,
                                email: $email_field.val(),
                                cart: custom_fields,
                                g_recaptcha_response: g_recaptcha_response,
                                force_update_customer: force_update_customer
                            };

                            $this.addClass('bookme-loader').prop('disabled',true);
                            $.ajax({
                                type: 'POST',
                                url: Bookme.ajaxurl,
                                data: data,
                                dataType: 'json',
                                xhrFields: {withCredentials: true},
                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    // Error messages
                                    $errors.empty();
                                    $fields.removeClass('bookme-error');

                                    if (response.success) {
                                        // woocommerce payment
                                        if (Bookme.woocommerce.enabled) {
                                            var data = {
                                                action: 'bookme_add_to_wc_cart',
                                                csrf_token: Bookme.csrf_token,
                                                form_id: form_id
                                            };
                                            $.ajax({
                                                type: 'POST',
                                                url: Bookme.ajaxurl,
                                                data: data,
                                                dataType: 'json',
                                                xhrFields: {withCredentials: true},
                                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                success: function (response) {
                                                    if (response.success) {
                                                        window.location.href = Bookme.woocommerce.cart_url;
                                                    } else {
                                                        $this.removeClass('bookme-loader').prop('disabled',false);
                                                        render_time_step(undefined, response.error);
                                                    }
                                                }
                                            });
                                        } else {
                                            // local payment
                                            if ($('.bookme-payment[value=local]', $booking_wrapper).is(':checked') || $('.bookme-coupon-free', $booking_wrapper).is(':checked') || $('.bookme-payment:checked', $booking_wrapper).val() == undefined) {
                                                e.preventDefault();

                                                $.ajax({
                                                    type: 'POST',
                                                    url: Bookme.ajaxurl,
                                                    data: {
                                                        action: 'bookme_save_cart_bookings',
                                                        csrf_token: Bookme.csrf_token,
                                                        form_id: form_id
                                                    },
                                                    dataType: 'json',
                                                    xhrFields: {withCredentials: true},
                                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                    success: function (response) {
                                                        if (response.success) {
                                                            render_done_step();
                                                        } else if (response.failed_cart_key != undefined) {
                                                            if (Bookme.cart.enabled) {
                                                                render_cart_step(undefined, {
                                                                    failed_key: response.failed_cart_key,
                                                                    message: response.error
                                                                });
                                                            } else {
                                                                render_time_step(undefined, response.error);
                                                            }
                                                        }
                                                    }
                                                });

                                            } else if ($('.bookme-payment[value=card]', $booking_wrapper).is(':checked')) {
                                                // card payment
                                                if($('.bookme-payment[data-tab=stripe]', $booking_wrapper).is(':checked')) {
                                                    // stripe SCA
                                                    var $form = $booking_wrapper.find('.bookme-stripe');

                                                    $.ajax({
                                                        type: 'POST',
                                                        url: Bookme.ajaxurl,
                                                        data: {
                                                            action: 'bookme_stripe_create_intent',
                                                            csrf_token: Bookme.csrf_token,
                                                            form_id: form_id
                                                        },
                                                        dataType: 'json',
                                                        xhrFields: {withCredentials: true},
                                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                        success: function (response) {
                                                            if (response.success) {
                                                                stripe.handleCardPayment(
                                                                    response.intent_secret,
                                                                    stripe_card
                                                                ).then(function (result) {
                                                                    if (result.error) {
                                                                        $.ajax({
                                                                            type: 'POST',
                                                                            url: Bookme.ajaxurl,
                                                                            data: {
                                                                                action: 'bookme_stripe_failed_payment',
                                                                                csrf_token: Bookme.csrf_token,
                                                                                form_id: form_id,
                                                                                intent_id: response.intent_id
                                                                            },
                                                                            dataType: 'json',
                                                                            xhrFields: {withCredentials: true},
                                                                            crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                                            success: function (response) {
                                                                                if (response.success) {
                                                                                    $this.removeClass('bookme-loader').prop('disabled',false);
                                                                                    $form.find('.bookme-card-error').text(result.error.message);
                                                                                }
                                                                            }
                                                                        });
                                                                    } else {
                                                                        $.ajax({
                                                                            type: 'POST',
                                                                            url: Bookme.ajaxurl,
                                                                            data: {
                                                                                action: 'bookme_stripe_process_payment',
                                                                                csrf_token: Bookme.csrf_token,
                                                                                form_id: form_id,
                                                                                intent_id: response.intent_id
                                                                            },
                                                                            dataType: 'json',
                                                                            xhrFields: {withCredentials: true},
                                                                            crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                                            success: function (response) {
                                                                                if (response.success) {
                                                                                    render_done_step();
                                                                                }
                                                                            }
                                                                        });
                                                                    }
                                                                });
                                                            }else if (response.failed_cart_key != undefined) {
                                                                if (Bookme.cart.enabled) {
                                                                    render_cart_step(undefined, {
                                                                        failed_key: response.failed_cart_key,
                                                                        message: response.error
                                                                    });
                                                                } else {
                                                                    render_time_step(undefined, response.error);
                                                                }
                                                            } else {
                                                                $this.removeClass('bookme-loader').prop('disabled',false);
                                                                $form.find('.bookme-card-error').text(response.error);
                                                            }
                                                        }
                                                    });
                                                }
                                                else if ($('.bookme-payment[data-tab=razorpay]', $booking_wrapper).is(':checked')) {
                                                    // razorpay
                                                    var $form = $booking_wrapper.find('.bookme-razorpay');

                                                    $.ajax({
                                                        type: 'POST',
                                                        url: Bookme.ajaxurl,
                                                        data: {
                                                            action: 'bookme_razorpay_init',
                                                            csrf_token: Bookme.csrf_token,
                                                            form_id: form_id
                                                        },
                                                        dataType: 'json',
                                                        xhrFields: {withCredentials: true},
                                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                        success: function (response) {
                                                            if (response.success) {
// Checkout details as a json
                                                                var options = response.data;

                                                                // Boolean whether to show image inside a white frame. (default: true)
                                                                options.theme.image_padding = false;
                                                                options.modal = {
                                                                    ondismiss: function() {
                                                                        $this.removeClass('bookme-loader').prop('disabled',false);
                                                                        return false;
                                                                    },
                                                                    escape: false
                                                                };

                                                                /**
                                                                 * The entire list of Checkout fields is available at
                                                                 * https://docs.razorpay.com/docs/checkout-form#checkout-fields
                                                                 */
                                                                options.handler = function (response){
                                                                    // On success
                                                                    $.ajax({
                                                                        type: 'POST',
                                                                        url: Bookme.ajaxurl,
                                                                        data: {
                                                                            action: 'bookme_razorpay_process_payment',
                                                                            csrf_token: Bookme.csrf_token,
                                                                            form_id: form_id,
                                                                            razorpay_payment_id: response.razorpay_payment_id,
                                                                            razorpay_signature: response.razorpay_signature
                                                                        },
                                                                        dataType: 'json',
                                                                        xhrFields: {withCredentials: true},
                                                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                                        success: function (response) {
                                                                            if (response.success) {
                                                                                render_done_step();
                                                                            } else {
                                                                                $this.removeClass('bookme-loader').prop('disabled',false);
                                                                                $form.find('.bookme-card-error').text(response.error);
                                                                            }
                                                                        }
                                                                    });
                                                                };
                                                                var rzp = new Razorpay(options);
                                                                rzp.open();

                                                            } else if (response.failed_cart_key != undefined) {
                                                                if (Bookme.cart.enabled) {
                                                                    render_cart_step(undefined, {
                                                                        failed_key: response.failed_cart_key,
                                                                        message: response.error
                                                                    });
                                                                } else {
                                                                    render_time_step(undefined, response.error);
                                                                }
                                                            } else {
                                                                $this.removeClass('bookme-loader').prop('disabled',false);
                                                                $form.find('.bookme-card-error').text(response.error);
                                                            }
                                                        }
                                                    });
                                                }
                                                else {
                                                    // authorize.net
                                                    var card_action = 'bookme_authorize_net';
                                                    var $form = $booking_wrapper.find('.bookme-authorize-net');
                                                    e.preventDefault();

                                                    var data = {
                                                        action: card_action,
                                                        csrf_token: Bookme.csrf_token,
                                                        card: {
                                                            number: $form.find('input[name="card_number"]').val(),
                                                            cvc: $form.find('input[name="card_cvc"]').val(),
                                                            exp_month: $form.find('select[name="card_exp_month"]').val(),
                                                            exp_year: $form.find('select[name="card_exp_year"]').val()
                                                        },
                                                        form_id: form_id
                                                    };

                                                    $.ajax({
                                                        type: 'POST',
                                                        url: Bookme.ajaxurl,
                                                        data: data,
                                                        dataType: 'json',
                                                        xhrFields: {withCredentials: true},
                                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                        success: function (response) {
                                                            if (response.success) {
                                                                render_done_step();
                                                            } else if (response.failed_cart_key != undefined) {
                                                                if (Bookme.cart.enabled) {
                                                                    render_cart_step(undefined, {
                                                                        failed_key: response.failed_cart_key,
                                                                        message: response.error
                                                                    });
                                                                } else {
                                                                    render_time_step(undefined, response.error);
                                                                }
                                                            } else {
                                                                $this.removeClass('bookme-loader').prop('disabled',false);
                                                                $form.find('.bookme-card-error').text(response.error);
                                                            }
                                                        }
                                                    });
                                                }

                                            } else if ($('.bookme-payment[value=paypal]', $booking_wrapper).is(':checked')
                                                || $('.bookme-payment[value=2checkout]', $booking_wrapper).is(':checked')
                                                || $('.bookme-payment[value=mollie]', $booking_wrapper).is(':checked')
                                            ) {
                                                e.preventDefault();
                                                var $pay = $('.bookme-payment:checked', $booking_wrapper).val();
                                                $form = $('form.bookme-' + $pay + '-form', $booking_wrapper);
                                                $.ajax({
                                                    type: 'POST',
                                                    url: Bookme.ajaxurl,
                                                    xhrFields: {withCredentials: true},
                                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                    data: {
                                                        action: 'bookme_check_cart',
                                                        csrf_token: Bookme.csrf_token,
                                                        form_id: form_id
                                                    },
                                                    dataType: 'json',
                                                    success: function (response) {
                                                        if (response.success) {
                                                            $form.submit();
                                                        } else if (response.failed_cart_key != undefined) {
                                                            if (Bookme.cart.enabled) {
                                                                render_cart_step(undefined, {
                                                                    failed_key: response.failed_cart_key,
                                                                    message: response.error
                                                                });
                                                            } else {
                                                                render_time_step(undefined, response.error);
                                                            }
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }else{
                                        $this.removeClass('bookme-loader').prop('disabled',false);
                                        var $scroll_to = null;
                                        if (response.bookings_limit) {
                                            render_done_step(['bookings_limit'])
                                        } else {
                                            // reset google recaptcha
                                            if(g_recaptcha_id != null){
                                                grecaptcha.reset(
                                                    g_recaptcha_id
                                                )
                                            }
                                            if (response.full_name) {
                                                $full_name_error.html(response.full_name);
                                                $full_name_field.addClass('bookme-error');
                                                $scroll_to = $full_name_field;
                                            }
                                            if (response.first_name) {
                                                $first_name_error.html(response.first_name);
                                                $first_name_field.addClass('bookme-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $first_name_field;
                                                }
                                            }
                                            if (response.last_name) {
                                                $last_name_error.html(response.last_name);
                                                $last_name_field.addClass('bookme-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $last_name_field;
                                                }
                                            }
                                            if (response.phone) {
                                                $phone_error.html(response.phone);
                                                $phone_field.addClass('bookme-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $phone_field;
                                                }
                                            }
                                            if (response.email) {
                                                $email_error.html(response.email);
                                                $email_field.addClass('bookme-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $email_field;
                                                }
                                            }
                                            if (response.custom_fields) {
                                                $.each(response.custom_fields, function (key, fields) {
                                                    $.each(fields, function (field_id, message) {
                                                        var $custom_fields_collector = $('.bookme-custom-fields-wrapper[data-key="' + key + '"]', $booking_wrapper);
                                                        var $div = $('[data-id="' + field_id + '"]', $custom_fields_collector);
                                                        $div.find('.bookme-custom-field-error').html(message);
                                                        $div.find('.bookme-custom-field').addClass('bookme-error');
                                                        if ($scroll_to === null) {
                                                            $scroll_to = $div;
                                                        }
                                                    });
                                                });
                                            }
                                            if (response.g_recaptcha_response) {
                                                $g_recaptcha_error.html(response.g_recaptcha_response);
                                                if ($scroll_to === null) {
                                                    $scroll_to = $g_recaptcha;
                                                }
                                            }
                                            if (response.customer) {
                                                $customer_modal
                                                    .find('.bookme-modal-body').html(response.customer).end()
                                                    .addClass('bookme-modal-show')
                                                ;
                                            }
                                        }
                                        if ($scroll_to !== null) {
                                            scrollTo($scroll_to);
                                        }
                                    }
                                }
                            });
                        });

                        $('.bookme-back', $booking_wrapper).on('click', function (e) {
                            e.preventDefault();
                            $(this).addClass('bookme-loader').prop('disabled',true);
                            if (Bookme.cart.enabled) {
                                render_cart_step();
                            } else {
                                render_time_step();
                            }
                        });
                    }
                }
            });
        }

        function render_done_step(error){
            if ((typeof error === 'undefined' || error.length == 0) && Bookme.final_step_url) {
                document.location.href = Bookme.final_step_url;
            } else {
                $.ajax({
                    url: Bookme.ajaxurl,
                    data: {
                        action: 'bookme_get_done_step',
                        csrf_token: Bookme.csrf_token,
                        form_id: form_id,
                        errors: error,
                        page_url: document.URL.split('#')[0]
                    },
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {
                        if (response.success) {
                            $booking_wrapper.html(response.html);
                            scrollTo($booking_wrapper);
                        }
                    }
                });
            }
        }

        $.fn.bookme_tooltip = function () {
            var tips = $(this);
            if (!tips.find('.bookme-tooltip').length) {
                tips.append($('<span class="bookme-tooltip"></span>'));
            }

            var data = tips.attr("title");
            tips.attr("title", "");
            tips.find('.bookme-tooltip').html(data);

            tips.onmouseover = tipShow;
            tips.onmouseout = tipHide;
            tips.on('mouseover', tipShow);
            tips.on('mouseout', tipHide);
        };

        function tipShow(e) {
            $(this).find('.bookme-tooltip').addClass('bookme-tooltip-show');
        }

        function tipHide() {
            $(this).find('.bookme-tooltip').removeClass('bookme-tooltip-show');
        }

        function scrollTo($elem) {
            var elemTop = $elem.offset().top;
            var scrollTop = $(window).scrollTop();
            if (elemTop < $(window).scrollTop() || elemTop > scrollTop + window.innerHeight) {
                $('html,body').animate({scrollTop: (elemTop - 24)}, 500);
            }
        }
    };
})(jQuery);