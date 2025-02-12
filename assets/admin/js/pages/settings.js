jQuery(function ($) {
    // Activate tab.
    $('a[href="#bookme_settings_' + Bookme.current_tab + '"]').tab('show');

    // General Tab
    var $default_country      = $('#bookme_phone_default_country'),
        $default_country_code = $('#bookme_default_country_code');

    $.each($.fn.intlTelInput.getCountryData(), function (index, value) {
        $default_country.append('<option value="' + value.iso2 + '" data-code="' + value.dialCode + '">' + value.name + ' +' + value.dialCode + '</option>');
    });
    $default_country.val(Bookme.default_country);

    $default_country.on('change', function () {
        $default_country_code.val($default_country.find('option:selected').data('code'));
    });

    // Company Tab
    if (Bookme.intlTelInput.enabled) {
        $('#bookme_company_phone').intlTelInput({
            preferredCountries: [Bookme.intlTelInput.country],
            initialCountry: Bookme.intlTelInput.country,
            geoIpLookup: function (callback) {
                jQuery.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            utilsScript: Bookme.intlTelInput.utils
        });
    }

    $('.company-image-selector').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var frame = wp.media({
            library: {type: 'image'},
            multiple: false
        });
        frame.on('select', function () {
            var selection = frame.state().get('selection').toJSON(),
                img_src;
            if (selection.length) {
                if (selection[0].sizes['thumbnail'] !== undefined) {
                    img_src = selection[0].sizes['thumbnail'].url;
                } else {
                    img_src = selection[0].url;
                }
                $('[name=bookme_company_logo_id]').val(selection[0].id);
                $('.company-image-selector img').attr('src', img_src);
                $(this).hide();
            }
        });

        frame.open();
    });

    // Cart Tab
    $('#bookme-cart-columns').sortable({
        axis : 'y',
        handle : '.bookme-reorder-icon'
    });

    // Payment Tab
    var $currency = $('#bookme_currency'),
        $formats  = $('#bookme_price_format');

    $currency.on('change', function () {
        $formats.find('option').each(function () {
            var decimals = this.value.match(/{price\|(\d)}/)[1],
                price    = Bookme.sample_price
            ;
            if (decimals < 3) {
                price = price.slice(0, -(decimals == 0 ? 4 : 3 - decimals));
            }
            this.innerHTML = this.value
                .replace('{symbol}', $currency.find('option:selected').data('symbol'))
                .replace(/{price\|\d}/, price)
            ;
        });
    }).trigger('change');

    $('#bookme_paypal_enabled').change(function () {
        if(this.value != 'disabled')
            $('.bookme-paypal').slideDown("slow");
        else
            $('.bookme-paypal').slideUp("slow");
    }).change();

    $('#bookme_authorize_net_enabled').change(function () {
        if(this.value != 'disabled')
            $('.bookme-authorize-net').slideDown("slow");
        else
            $('.bookme-authorize-net').slideUp("slow");
    }).change();

    $('#bookme_stripe_enabled').change(function () {
        if(this.value != 'disabled')
            $('.bookme-stripe').slideDown("slow");
        else
            $('.bookme-stripe').slideUp("slow");
    }).change();

    $('#bookme_razorpay_enabled').change(function () {
        if(this.value != 'disabled')
            $('.bookme-razorpay').slideDown("slow");
        else
            $('.bookme-razorpay').slideUp("slow");
    }).change();

    $('#bookme_2checkout_enabled').change(function () {
        if(this.value != 'disabled')
            $('.bookme-2checkout').slideDown("slow");
        else
            $('.bookme-2checkout').slideUp("slow");
    }).change();

    $('#bookme_mollie_enabled').change(function () {
        if(this.value != 'disabled')
            $('.bookme-mollie').slideDown("slow");
        else
            $('.bookme-mollie').slideUp("slow");
    }).change();

    var payment_order = [];
    $('.bm-payment-sortable').sortable({
        axis : 'y',
        handle : '.bookme-reorder-icon',
        helper: 'clone',
        update: function (event, ui) {
            payment_order = [];
            $('.bm-payment-sortable').find('.bookme-reorder-icon').each(function () {
                payment_order.push($(this).data('payment'));
            });
        }
    });

    // Notifications Tab
    if (Bookme.intlTelInput.enabled) {
        $('#bookme_twillio_phone_number').intlTelInput({
            preferredCountries: [Bookme.intlTelInput.country],
            initialCountry: Bookme.intlTelInput.country,
            geoIpLookup: function (callback) {
                jQuery.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            utilsScript: Bookme.intlTelInput.utils
        });
        $('#bookme_sms_admin_phone').intlTelInput({
            preferredCountries: [Bookme.intlTelInput.country],
            initialCountry: Bookme.intlTelInput.country,
            geoIpLookup: function (callback) {
                jQuery.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            utilsScript: Bookme.intlTelInput.utils
        });
    }

    // working hours
    $('.schedule-start').off().on('change',function(){
        var $this = $(this),
            $end_select = $this.closest('.input-group').find('.schedule-end'),
            start_time = $this.val() || "";

        // Hide end time options to keep them within 24 hours after start time.
        var parts = start_time.split(':');
        parts[0] = parseInt(parts[0]) + 24;
        var end_time = parts.join(':');
        var frag = document.createDocumentFragment();
        var old_value = $end_select.val();
        var new_value = null;
        $('option', $end_select).each(function () {
            if (this.value <= start_time || this.value > end_time) {
                var span = document.createElement('span');
                span.style.display = 'none';
                span.appendChild(this.cloneNode(true));
                frag.appendChild(span);
            } else {
                frag.appendChild(this.cloneNode(true));
                if (new_value === null || old_value == this.value) {
                    new_value = this.value;
                }
            }
        });
        $end_select.empty().append(frag).val(new_value);
    }).trigger('change');

    $('.schedule-day-off').off().on('change',function(){
        var $start = $(this).closest('.row').find('.schedule-start'),
            $end = $(this).closest('.row').find('.schedule-end');
        if($(this).is(':checked')) {
            $(this).closest('.row').find('.schedule-day-off-hide').addClass('div-disabled');
            $start.data('last-value', $start.val());
            $start.append($('<option value="" id="empty-value"></option>')).val("");
            $end.data('last-value', $end.val());
            $end.append($('<option value="" id="empty-value"></option>')).val("");
        } else {
            $(this).closest('.row').find('.schedule-day-off-hide').removeClass('div-disabled');
            $start.find('#empty-value').remove();
            $start.val($start.data('last-value'));
            $end.find('#empty-value').remove();
            $end.val($end.data('last-value'));
        }
    }).trigger('change');

    // Holidays
    var $panel = $('#bookme-holidays');
    bookme_print_cal($('.bookme-cal-wrap', $panel), new Date().getFullYear());

    $('#bookme_dayoff_nav_left',$panel).on('click',function () {
        bookme_print_cal($('.bookme-cal-wrap', $panel),parseInt($('#bookme_dayoff_nav_year').val()) - 1);
    });

    $('#bookme_dayoff_nav_right',$panel).on('click',function () {
        bookme_print_cal($('.bookme-cal-wrap', $panel),parseInt($('#bookme_dayoff_nav_year').val()) + 1);
    });

    $panel.on('click', '.popover-close', function () {
        $(this).closest('.popover').popover('hide');
    });

    $panel.on('click', '.bookme-cal-popover', function () {
        $('.popover').popover('hide');

        var $div = $(this);
        var ch  = $div.hasClass('bookme-dayoff') ? 'checked' : '';
        var ch2 = $div.hasClass('bookme-dayoff-repeat') ? 'checked' : '';
        var di  = ch ? '' : 'disabled';
        var date = $div.data('date');

        $('.bookme-cal-popover').removeClass('selected');
        $div.addClass('selected');

        var $popup = $("<div><div class='checkbox m-b-10'><input type='checkbox' id='bm-dayoff-" + date + "' class='bm-check bm-dayoff' " + ch + "><label for='bm-dayoff-" + date + "'><span class='checkbox-icon'></span> " + Bookme.we_are_not_working + "</label></div><br><div class='checkbox m-b-10'><input type='checkbox' id='bm-dayoff-repeat-" + date + "' " + di + " class='bm-check bm-dayoff-repeat' " + ch2 + "><label for='bm-dayoff-repeat-" + date + "'><span class='checkbox-icon'></span> " + Bookme.repeat + "</label></div><br><button type='button' class='btn-icon btn-primary m-r-5 ajax-save-holiday'><i class='icon-feather-check'></i></button><button type='button' class='btn-icon popover-close'><i class='icon-feather-x'></i></button></div>");

        var $day_off = $popup.find('.bm-dayoff');
        var $repeat  = $popup.find('.bm-dayoff-repeat');

        $popup.find('.popover-close').on('click', function () {
            $div.popover('hide');
        });

        $div
            .popover({
                html: true,
                container: '#bookme-holidays',
                placement: 'bottom',
                content: function() {
                    return $popup;
                },
                trigger: 'manual'
            })
            .popover('show');

        $day_off.on('change', function () {
            if ($day_off.prop('checked')) {
                $repeat.prop('disabled', false);
            } else {
                $repeat.prop('checked', false).prop('disabled', true);
            }
        });

        $popup.find('.ajax-save-holiday').on('click', function () {
            var $this = $(this),
                year = date.split('-')[2],
                day_off = $day_off.prop('checked'),
                repeat = $repeat.prop('checked');


            var options = {
                action: 'bookme_company_holidays',
                csrf_token: Bookme.csrf_token,
                id: $div.data('id') || 0,
                holiday: day_off,
                repeat: repeat,
                day: date
            };

            $this.addClass('bookme-loader').prop('disabled',true);

            $.get(
                ajaxurl,
                options,
                function (response) {
                    $this.removeClass('bookme-loader').prop('disabled',false);
                    $div.popover('hide');
                    if(response){
                        $('.bookme-cal-wrap', $panel).data('holidays',response);
                        bookme_print_cal($('.bookme-cal-wrap', $panel), year);
                    }
                },
                'json'
            );
        });
    });

    $('.bm-ajax-form').on('submit', function (e) {
        e.stopPropagation();
        e.preventDefault();

        var $form = $(this),
            data  = $form.serializeArray(),
            $button = $form.find('button[type="submit"]'),
            tab = $form.data('tab');

        if(tab == 'company'){
            var $company_phone = $('#bookme_company_phone', $form),
                phone;
            try {
                phone = Bookme.intlTelInput.enabled ? $company_phone.intlTelInput('getNumber') : $company_phone.val();
                if (phone == '') {
                    phone = $company_phone.val();
                }
            } catch (error) {  // In case when intlTelInput can't return phone number.
                phone = $company_phone.val();
            }
            data.push({name: 'bookme_company_phone',  value: phone});

        } else if(tab == 'notifications'){
            var $twilio_phone = $('#bookme_twillio_phone_number', $form),
                $admin_phone = $('#bookme_sms_admin_phone', $form),
                phone1, phone2;
            try {
                phone1 = Bookme.intlTelInput.enabled ? $twilio_phone.intlTelInput('getNumber') : $twilio_phone.val();
                if (phone1 == '') {
                    phone1 = $twilio_phone.val();
                }
            } catch (error) {  // In case when intlTelInput can't return phone number.
                phone1 = $twilio_phone.val();
            }
            try {
                phone2 = Bookme.intlTelInput.enabled ? $admin_phone.intlTelInput('getNumber') : $admin_phone.val();
                if (phone2 == '') {
                    phone2 = $admin_phone.val();
                }
            } catch (error) {  // In case when intlTelInput can't return phone number.
                phone2 = $admin_phone.val();
            }
            data.push({name: 'bookme_twillio_phone_number',  value: phone1});
            data.push({name: 'bookme_sms_admin_phone',  value: phone2});
        } else if(tab == 'payments'){
            data.push({name: 'bookme_payment_order',  value: payment_order});
        }

        data.push({name: 'action', value: 'bookme_update_settings'});
        data.push({name: 'tab', value: tab});

        $button.addClass('bookme-loader').prop('disabled',true);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            xhrFields: {withCredentials: true},
            crossDomain: 'withCredentials' in new XMLHttpRequest(),
            success: function (response) {
                $button.removeClass('bookme-loader').prop('disabled',false);
                if (response.success) {
                    bm_alert(response.data.success);
                } else {
                    bm_alert(response.data.error,'error');
                }
            }
        });
    });
});