jQuery(function ($) {

    // Activate tab.
    $('a[href="#bookme_notifications_' + Bookme.current_tab + '"]').tab('show');

    // submit notification form
    $('.bm-ajax-form').on('submit', function (e) {
        e.preventDefault();

        // TinyMCE will now save the data into textarea
        tinyMCE.triggerSave();

        var $form = $(this),
            data = $form.serializeArray(),
            $button = $form.find('button[type="submit"]'),
            tab = $form.data('tab');

        data.push({name: 'action', value: 'bookme_update_notifications'});
        data.push({name: 'tab', value: tab});

        $button.addClass('bookme-loader').prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            xhrFields: {withCredentials: true},
            crossDomain: 'withCredentials' in new XMLHttpRequest(),
            success: function (response) {
                $button.removeClass('bookme-loader').prop('disabled', false);
                if (response.success) {
                    bm_alert(Bookme.saved);
                } else {
                    bm_alert(response.data.error);
                }
            }
        });
    });

    // test email notification
    var $test_panel = $('#bookme-test-notifications-panel'),
        $notifications = $('#bookme-test-notification');

    $notifications.multiselect({
        texts: {
            placeholder: $notifications.data('placeholder'), // text to use in dummy input
            selectedOptions: ' ' + $notifications.data('selected'),      // selected suffix text
            selectAll: $notifications.data('selectall'),     // select all text
            unselectAll: $notifications.data('unselectall'),   // unselect all text
            noneSelected: $notifications.data('nothing'),   // None selected text
            allSelected: $notifications.data('allselected')
        },
        showCheckbox: false,  // display the checkbox to the user
        selectAll: true, // add select all option
        minHeight: 20,
        maxPlaceholderOpts: 1
    });

    $('#bm-test-email').on('click', function (e) {
        bm_show_sidepanel($test_panel);
    });

    $('.ajax-send-notifications').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        var $form = $test_panel.find('form'),
            data = $form.serializeArray(),
            $button = $(this);

        data.push({name: 'action', value: 'bookme_test_notifications'});
        if(validateForm($form)) {
            $button.addClass('bookme-loader').prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    $button.removeClass('bookme-loader').prop('disabled', false);
                    if (response.success) {
                        bm_alert(Bookme.test_send);
                    } else {
                        bm_alert(response.data.error);
                    }
                }
            });
        }
    });

    // test sms notification
    var $test_sms = $('#bm-test-sms'),
        $test_sms_phone = $('#bookme_test_sms_phone');

    if (Bookme.intlTelInput.enabled) {
        $test_sms_phone.intlTelInput({
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

    $test_sms.on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        var $button = $(this), phone;

        try {
            phone = Bookme.intlTelInput.enabled ? $test_sms_phone.intlTelInput('getNumber') : $test_sms_phone.val();
            if (phone == '') {
                phone = $test_sms_phone.val();
            }
        } catch (error) {  // In case when intlTelInput can't return phone number.
            phone = $test_sms_phone.val();
        }

        if(phone != '') {
            $button.addClass('bookme-loader').prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    csrf_token: Bookme.csrf_token,
                    action: 'bookme_test_sms_notifications',
                    phone: phone
                },
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    $button.removeClass('bookme-loader').prop('disabled', false);
                    if (response.success) {
                        bm_alert(response.message);
                    } else {
                        bm_alert(response.message,'error');
                    }
                }
            });
        }
    });
});