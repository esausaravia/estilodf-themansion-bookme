jQuery(function ($) {
    var $employee_wrapper = $('.employees-wrapper'),
        $employee_tbody = $('#employees-tbody'),
        $no_result = $('.bm-no-result'),
        $employee_count = $('.bm-employee-count');

    $employee_tbody.sortable({
        helper: function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        },
        axis: 'y',
        handle: '.bookme-reorder-icon',
        update: function (event, ui) {
            var data = [];
            $employee_tbody.children('tr').each(function () {
                var $this = $(this);
                var position = $this.data('id');
                data.push(position);
            });
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {action: 'bookme_update_employee_position', position: data, csrf_token: Bookme.csrf_token}
            });
        }
    });

    $('#bm-delete-button').on('click', function (e) {
        if (confirm(Bookme.are_you_sure)) {
            var $for_delete = $('.bm-check:checked'),
                data = {action: 'bookme_delete_employees', csrf_token: Bookme.csrf_token},
                employees = [],
                $row = [],
                $this = $(this);

            $for_delete.each(function () {
                var row = $(this).parents('tr');
                $row.push(row);
                employees.push(this.value);
            });
            data['ids[]'] = employees;

            $this.addClass('bookme-loader').prop('disabled',true);
            $.post(ajaxurl, data, function (response) {
                $this.removeClass('bookme-loader').prop('disabled',false);
                if (response.success) {
                    $.each($row.reverse(), function (index) {
                        $(this).delay(500 * index).fadeOut(200, function () {
                            $(this).remove();
                            $employee_count.text($employee_tbody.children().length);
                            if($employee_tbody.children().length == 0){
                                $no_result.show();
                                $employee_wrapper.hide();
                            }
                        });
                    });
                }
            });
        }
    });

    window.new_employee = function ($panel) {

        var $staff_full_name = $('#bookme-full-name', $panel),
            $staff_wp_user = $('#bookme-wp-user', $panel),
            $staff_email = $('#bookme-email', $panel),
            $staff_phone = $('#bookme-phone', $panel)
            ;

        if (Bookme.intlTelInput.enabled) {
            $staff_phone.intlTelInput({
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

        $staff_wp_user.on('change', function () {
            if (this.value) {
                $staff_full_name.val($staff_wp_user.find(':selected').text());
                $staff_email.val($staff_wp_user.find(':selected').data('email'));
            }
        });

        // Save staff member details.
        $('.ajax-add-employee', $panel).on('click', function (e) {
            e.preventDefault();
            var $form = $panel.find('form'),
                data = $form.serializeArray(),
                $staff_phone = $('#bookme-phone', $form),
                phone,
                $this = $(this);
            try {
                phone = Bookme.intlTelInput.enabled ? $staff_phone.intlTelInput('getNumber') : $staff_phone.val();
                if (phone == '') {
                    phone = $staff_phone.val();
                }
            } catch (error) {  // In case when intlTelInput can't return phone number.
                phone = $staff_phone.val();
            }
            data.push({name: 'action', value: 'bookme_add_employee'});
            data.push({name: 'phone', value: phone});

            if (validateForm($form)) {
                $this.addClass('bookme-loader').prop('disabled',true);
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {
                        $this.removeClass('bookme-loader').prop('disabled',false);
                        if (response.success) {
                            $no_result.hide();
                            $employee_wrapper.show();
                            $employee_tbody.append(response.data);
                            bm_alert(Bookme.saved);
                            if ($form.hasClass('bm-add-employee')) {
                                $employee_count.text($employee_tbody.children().length);
                            }
                            $.slidePanel.hide();
                        }
                    }
                });
            }
        });
    };

    window.bookmeSidePanelLoaded = function ($edit_form) {
        var emp_id = $('input[name=id]', $edit_form).val(),
            $staff_full_name = $('#bookme-full-name', $edit_form),
            $staff_wp_user = $('#bookme-wp-user', $edit_form),
            $staff_email   = $('#bookme-email', $edit_form),
            $staff_phone   = $('#bookme-phone', $edit_form),
            $services   = $('#bookme-services', $edit_form)
            ;

        $('.employee-image-selector', $edit_form).on('click', function (e) {
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
                    $edit_form.find('[name=attachment_id]').val(selection[0].id);
                    $('.employee-image-selector img').attr('src', img_src);
                    $(this).hide();
                }
            });

            frame.open();
        });

        $services.multiselect({
            texts: {
                placeholder: $services.data('placeholder'), // text to use in dummy input
                selectedOptions: ' ' + $services.data('selected'),      // selected suffix text
                selectAll: $services.data('selectall'),     // select all text
                unselectAll: $services.data('unselectall'),   // unselect all text
                noneSelected: $services.data('nothing'),   // None selected text
                allSelected: $services.data('allselected')
            },
            showCheckbox: false,  // display the checkbox to the user
            selectAll: true, // add select all option
            minHeight: 20,
            maxPlaceholderOpts: 1
        });

        $services.on('change', function () {
            var values = $(this).val() || [];
            //console.log(values);
            $('.bm-service-group').hide();
            $('.bm-service-group input').prop('disabled',true);

            values.forEach(function (val) {
                $('.bm-service-group-' + val).show();
                $('.bm-service-group-' + val + ' input').prop('disabled',false);
            });
        });

        if (Bookme.intlTelInput.enabled) {
            $staff_phone.intlTelInput({
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

        $staff_wp_user.on('change', function () {
            if (this.value) {
                $staff_full_name.val($staff_wp_user.find(':selected').text());
                $staff_email.val($staff_wp_user.find(':selected').data('email'));
            }
        });

        $('.bm-add-break').popover({
            container: '.bookme-page-wrapper .slidePanel-inner',
            html: true,
            placement: 'top',
            trigger: 'manual',
            content: function () {
                return $($(this).data('popover-content')).html()
            }
        }).on('click', function () {
            $(this).popover('toggle');

            var $popover = $('#' + $(this).attr('aria-describedby')),
                working_start = $(this).closest('.row').find('.schedule-start').val(),
                $break_start = $popover.find('.break-start'),
                $break_end = $popover.find('.break-end'),
                working_start_time = working_start.split(':'),
                working_start_hours = parseInt(working_start_time[0], 10),
                break_start_hours = working_start_hours + 1;
            if (break_start_hours < 10) {
                break_start_hours = '0' + break_start_hours;
            }
            var break_end_hours = working_start_hours + 2;
            if (break_end_hours < 10) {
                break_end_hours = '0' + break_end_hours;
            }
            var break_end_hours_str = break_end_hours + ':' + working_start_time[1] + ':' + working_start_time[2],
                break_start_hours_str = break_start_hours + ':' + working_start_time[1] + ':' + working_start_time[2];

            $break_start.val(break_start_hours_str);
            $break_end.val(break_end_hours_str);

            reset_breaks($break_start, $break_end);
        });

        $('.schedule-start', $edit_form).off().on('change',function(){
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

        $('.schedule-day-off', $edit_form).off().on('change',function(){
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

        // handle breaks
        $edit_form.off()
            .on('change', '.break-start', function () {
                var $start = $(this);
                var $end = $start.parents('.input-group').find('.break-end');
                reset_breaks($start, $end);
            })
            .on('click', '.ajax-save-break', function (e) {
                var $popover = $(this).closest('.popover'),
                    $schedule_id = $popover.find('.break-start').data('id'),
                    data = {
                        action: 'bookme_schedule_save_break',
                        staff_schedule_id: $schedule_id,
                        start_time: $popover.find('.break-start > option:selected').val(),
                        end_time: $popover.find('.break-end > option:selected').val(),
                        working_start: $('#schedule-start-'+$schedule_id+' > option:selected').val(),
                        working_end: $('#schedule-end-'+$schedule_id+' > option:selected').val(),
                        csrf_token: Bookme.csrf_token
                    },
                    break_id = '',
                    $this = $(this);

                if ($this.data('break-id')) {
                    data['break_id'] = break_id = $this.data('break-id');
                }

                $this.addClass('bookme-loader').prop('disabled',true);
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        $this.removeClass('bookme-loader').prop('disabled',false);
                        if (response.success) {
                            if (response['content']) {
                                var $new_break_item = $(response['content']);
                                $new_break_item
                                    .hide()
                                    .appendTo($('#schedule-breaks-wrapper-'+$schedule_id))
                                    .fadeIn('slow');
                            } else if (response.data.interval) {
                                $('.bm-break-'+break_id)
                                    .find('.bm-schedule-break')
                                    .text(response.data.interval);
                            }
                            $popover.popover('hide');
                        } else {
                            bm_alert(response.data.message,'error');
                        }
                    }
                });

                return false;
            })
            .on('click', '.delete-break', function () {
                var $schedule_break = $(this).closest('.schedule-break'),
                    $this = $(this);
                if (confirm(Bookme.are_you_sure)) {
                    $this.addClass('bookme-loader').prop('disabled',true);
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {action: 'bookme_delete_schedule_break', id: $schedule_break.data('break-id'), csrf_token: Bookme.csrf_token},
                        success: function (response) {
                            if (response.success) {
                                $schedule_break.remove();
                            }
                        }
                    });
                }
            })
            .on('click', '.bm-schedule-break', function () {
                $(this).popover({
                    container: '.bookme-page-wrapper .slidePanel-inner',
                    html: true,
                    placement: 'top',
                    trigger: 'manual',
                    content: function () {
                        return $($(this).data('popover-content')).html()
                    }
                });
                $(this).popover('toggle');
                var $popover = $('#' + $(this).attr('aria-describedby')),
                    $break_start = $popover.find('.break-start'),
                    $break_end = $popover.find('.break-end');

                var interval = $(this).html().trim().split(' - ');
                $break_start.val($break_start.find('option').filter(function () {
                    return $(this).html() == interval[0];
                }).val());
                $break_end.val($break_end.find('option').filter(function () {
                    return $(this).html() == interval[1];
                }).val());

                reset_breaks($break_start, $break_end, true);

            }).on('click', '.bm-popover-close', function () {
                $(this).closest('.popover').popover('hide');
            }).on('click', '.slidePanel-close', function () {
                $.slidePanel.hide();
            });
        $('.break-start', $edit_form).trigger('change');

        // Update employee
        $('.ajax-update-employee', $edit_form).on('click',function(e){
            e.preventDefault();
            var $form = $edit_form.find('form'),
                data  = $form.serializeArray(),
                $staff_phone = $('#bookme-phone',$form),
                $this = $(this),
                phone;
            try {
                phone = Bookme.intlTelInput.enabled ? $staff_phone.intlTelInput('getNumber') : $staff_phone.val();
                if (phone == '') {
                    phone = $staff_phone.val();
                }
            } catch (error) {  // In case when intlTelInput can't return phone number.
                phone = $staff_phone.val();
            }
            data.push({name: 'action', value: 'bookme_update_employee'});
            data.push({name: 'phone',  value: phone});
            $this.addClass('bookme-loader').prop('disabled',true);
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    $this.removeClass('bookme-loader').prop('disabled',false);
                    if (response.success) {
                        // update employees table
                        $.get(ajaxurl, {
                            action: 'bookme_get_employees',
                            csrf_token: Bookme.csrf_token
                        }, function (response) {
                            if (response.success) {
                                $employee_tbody.html(response.data.html);
                            }
                        });

                        bm_alert(Bookme.saved);
                    } else {
                        bm_alert(response.data.error);
                    }
                }
            });
        });

        // Delete employee
        $('.ajax-delete-employee', $edit_form).on('click', function (e) {
            e.preventDefault();
            var $this = $(this);
            if (confirm(Bookme.are_you_sure)) {
                $this.addClass('bookme-loader').prop('disabled',true);
                $.post(ajaxurl, {
                    action: 'bookme_delete_employees',
                    'ids[]': emp_id,
                    csrf_token: Bookme.csrf_token
                }, function () {
                    $.slidePanel.hide();
                    $('#bookme-employee-' + emp_id).remove();
                    $employee_count.text($employee_tbody.children().length);
                });
            }
        });
    };

    function reset_breaks($start, $end, force_keep_values) {
        var id = $start.data('id'),
            $working_start = $('#schedule-start-'+id),
            $working_end = $('#schedule-end-'+id),
            frag1 = document.createDocumentFragment(),
            frag2 = document.createDocumentFragment(),
            old_value = $start.val(),
            new_value = null;

        $('option', $start).each(function () {
            if ((this.value < $working_start.val() || this.value >= $working_end.val()) && (!force_keep_values || this.value != old_value)) {
                var span = document.createElement('span');
                span.style.display = 'none';
                span.appendChild(this.cloneNode(true));
                frag1.appendChild(span);
            } else {
                frag1.appendChild(this.cloneNode(true));
                if (new_value === null || old_value == this.value) {
                    new_value = this.value;
                }
            }
        });
        $start.empty().append(frag1).val(new_value);

        // Hide end time options with value less than in the start time.
        old_value = $end.val();
        new_value = null;
        $('option', $end).each(function () {
            if ((this.value <= $start.val() || this.value > $working_end.val()) && (!force_keep_values || this.value != old_value)) {
                var span = document.createElement('span');
                span.style.display = 'none';
                span.appendChild(this.cloneNode(true));
                frag2.appendChild(span);
            } else {
                frag2.appendChild(this.cloneNode(true));
                if (new_value === null || old_value == this.value) {
                    new_value = this.value;
                }
            }
        });
        $end.empty().append(frag2).val(new_value);
    }

    window.holidays_panel = function ($panel) {
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

        $panel.find('.bookme-page-wrapper').on('click', '.bookme-cal-popover', function () {
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
                    container: '.slidePanel .bookme-page-wrapper',
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
                    action: 'bookme_holidays_update',
                    csrf_token: Bookme.csrf_token,
                    id: $div.data('id') || 0,
                    holiday: day_off,
                    repeat: repeat,
                    day: date,
                    staff_id: $('.bookme-cal-wrap', $panel).data('id')
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
    };
});