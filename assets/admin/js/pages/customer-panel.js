var $customer_panel = jQuery('#bm-customer-sidepanel'),
    $customer_new_title = jQuery('#bm-add-customer-title'),
    $customer_edit_title = jQuery('#bm-edit-customer-title'),
    $id = jQuery('#bm-customer-id'),
    $wp_user = jQuery('#bm-wp-user'),
    $first_name = jQuery('#bm-first-name'),
    $last_name = jQuery('#bm-last-name'),
    $full_name = jQuery('#bm-full-name'),
    $email = jQuery('#bm-email'),
    $phone = jQuery('#bm-phone'),
    $notes = jQuery('#bm-notes'),
    $save_button = jQuery('#ajax-save-customer'),
    callback;

// Init intlTelInput.
if (BookmeCustomers.intlTelInput.enabled) {
    $phone.intlTelInput({
        preferredCountries: [BookmeCustomers.intlTelInput.country],
        initialCountry: BookmeCustomers.intlTelInput.country,
        geoIpLookup: function (callback) {
            jQuery.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : '';
                callback(countryCode);
            });
        },
        utilsScript: BookmeCustomers.intlTelInput.utils
    });
}

/**
 * Save customer.
 */
$save_button.on('click', function (e) {
    e.preventDefault();
    var $form = $customer_panel.find('form'),
        $this = jQuery(this),
        data = $form.serializeArray(),
        phone;

    try {
        phone = BookmeCustomers.intlTelInput.enabled ? $phone.intlTelInput('getNumber') : $phone.val();
        if (phone == '') {
            phone = $phone.val();
        }
    } catch (error) {  // In case when intlTelInput can't return phone number.
        phone = $phone.val();
    }

    data.push({name: 'action', value: 'bookme_save_customer'});
    data.push({name: 'phone', value: phone});

    if (validateForm($form)) {
        $this.addClass('bookme-loader').prop('disabled', true);
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    if (callback) {
                        // Call callback.
                        callback(response.customer);
                    }
                    bm_hide_sidepanel($customer_panel);
                } else {
                    bm_alert(response.errors, 'error');
                }
                $this.removeClass('bookme-loader').prop('disabled', false);
            }
        });
    }
});

function show_customer_dialog(data, _callback) {
    if (data) {
        $id.val(data.id);
        $wp_user.val(data.wp_user_id);
        $first_name.val(data.first_name);
        $last_name.val(data.last_name);
        $full_name.val(data.full_name);
        $email.val(data.email);
        $phone.val(data.phone);
        $notes.val(data.notes);

        $customer_edit_title.show();
        $customer_new_title.hide();
    } else {
        $id.val(null);
        $wp_user.val('');
        $first_name.val('');
        $last_name.val('');
        $full_name.val('');
        $email.val('');
        $phone.val('');
        $notes.val('');

        $customer_edit_title.hide();
        $customer_new_title.show();
    }

    callback = _callback;
    bm_show_sidepanel($customer_panel);
}