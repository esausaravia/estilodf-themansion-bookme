jQuery(function ($) {
    var $calendar = $('.bookme-calendar'),
        $primary_color = $('.bm-primary-color-wrapper input'),
        $secondary_color = $('.bm-secondary-color-wrapper input'),
        $progress_bar = $('#bookme_show_progress_bar'),
        $employee_with_price = $('#bookme_employee_name_with_price'),
        $service_with_duration = $('#bookme_service_name_with_duration'),
        $form_layout = $('#bookme_form_layout'),
        $custom_css_button = $('#bookme-custom-css-button'),
        $custom_css_panel = $('#bookme-custom-css-panel'),
        $appearance_form = $('#bm-appearance-form');

    bm_init_color_picker('.bm-primary-color-wrapper');
    bm_init_color_picker('.bm-secondary-color-wrapper');

    // init calendar
    $calendar.clndr({
        monthsLabel: Bookme.months,
        daysOfTheWeek: Bookme.daysShort,
        weekOffset: Bookme.start_of_week,
        multiDayEvents: {
            startDate: 'startDate',
            endDate: 'endDate'
        },
        showAdjacentMonths: true,
        adjacentDaysChangeMonth: false
    });

    $primary_color.on('change', change_color).trigger('change');
    $secondary_color.on('change', change_color).trigger('change');

    $progress_bar.on('change', function () {
        $('.bookme-steps').toggle($(this).is(':checked'));
    }).trigger('change');

    $service_with_duration.on('change', function () {
        var service = $('.bookme-service').val();
        if (service) {
            $('.bookme-service').val(service * -1);
        }
        $('.service-with-duration').toggle($service_with_duration.is(":checked"));
        $('.service-without-duration').toggle(!$service_with_duration.is(":checked"));
    }).trigger('change');

    $employee_with_price.on('change', function () {
        var staff = $('.bookme-employee').val();
        if (staff) {
            $('.bookme-employee').val(staff * -1);
        }
        $('.staff-with-price').toggle($employee_with_price.is(":checked"));
        $('.staff-without-price').toggle(!$employee_with_price.is(":checked"));
    }).trigger('change');

    $form_layout.on('change', function () {
        if($(this).val() == 1)
            $('.bookme-booking-service-step').find('.bookme-row').addClass('bookme-layout-1');
        else
            $('.bookme-booking-service-step').find('.bookme-row').removeClass('bookme-layout-1');
    }).trigger('change');

    $custom_css_button.on('click', function (e) {
        e.preventDefault();
        bm_show_sidepanel($custom_css_panel);
    });

    // Custom CSS.
    $('.bm-save-custom-css').on('click', function (e) {
        var $custom_css = $('#bookme-custom-css-field').val(),
            $this = $(this);
        Bookme.custom_css = $custom_css;

        $this.addClass('bookme-loader').prop('disabled',true);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bookme_save_custom_css',
                csrf_token: Bookme.csrf_token,
                custom_css: $custom_css
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    bm_hide_sidepanel($custom_css_panel);
                    bm_alert(response.data.message);
                }
            },
            complete: function () {
                $this.removeClass('bookme-loader').prop('disabled',false);
            }
        });
    });

    $custom_css_panel.on('sidePanel.hide', function (e) {
        $('#bookme-custom-css-field').val(Bookme.custom_css);
    });

    $('#bookme-custom-css-field').keydown(function (e) {
        if (e.keyCode === 9) { //tab button
            var start = this.selectionStart;
            var end = this.selectionEnd;

            var $this = $(this);
            var value = $this.val();

            $this.val(value.substring(0, start)
                + "\t"
                + value.substring(end));

            this.selectionStart = this.selectionEnd = start + 1;
            e.preventDefault();
        }
    });

    $appearance_form.on('submit',function (e) {
        e.stopPropagation();
        e.preventDefault();

        var $form = $(this),
            data  = $form.serializeArray(),
            $button = $form.find('button[type="submit"]');

        data.push({name: 'action', value: 'bookme_update_appearance'});

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
                    bm_alert(Bookme.saved);
                } else {
                    bm_alert(response.data.error);
                }
            }
        });
    });

    function change_color() {
        var primary_color = $primary_color.val(),
            secondary_color = $secondary_color.val();

        $('.bookme-button').attr('style', 'background: ' + primary_color+"!important; color:"+ secondary_color+"!important;");

        var style = "<style>" +
            ".bookme-steps > li.bookme-steps-is-active{color:" + primary_color+"!important}" +
            ".clndr .clndr-controls{background-color:" + primary_color+"!important}" +
            ".clndr .clndr-controls .clndr-month{color:" + secondary_color+"!important}" +
            ".clndr .clndr-controls .clndr-control-button span{color:" + secondary_color+"!important}" +
            ".clndr .clndr-table tr .day:hover{background-color:"+primary_color+"!important;color:"+secondary_color+"!important}" +
            ".bookme-steps > li.bookme-steps-is-active:before{border-color:"+primary_color+"!important;color:"+primary_color+"!important}" +
            "</style>";
        $('#bm-js-style').html(style);
    }
});