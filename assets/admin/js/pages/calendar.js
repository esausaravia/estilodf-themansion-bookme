jQuery(function ($) {

    var $fullCalendar = $('#bookme-calendar .bm-full-calendar'),
        $tabs = $('.bookme-calendar-employee'),
        $deleteDialog = $('#bm-booking-delete-dialog'),
        firstHour = new Date().getHours(),
        staffMembers = [],
        lastView = getCookie('bookme_cal_view'),
        views = 'month agendaWeek agendaDay';

    if (views.indexOf(lastView) == -1) {
        lastView = 'month';
    }


    /**
     * Calculate height of FullCalendar.
     *
     * @return {number}
     */
    function heightFC() {
        var window_height = $(window).height(),
            wp_admin_bar_height = $('#wpadminbar').height(),
            bookme_calendar_tabs_height = $('#bookme-calendar .tabbable').outerHeight(true),
            height_to_reduce = wp_admin_bar_height + bookme_calendar_tabs_height,
            $wrap = $('#wpbody-content .wrap');

        if ($wrap.css('margin-top')) {
            height_to_reduce += parseInt($wrap.css('margin-top').replace('px', ''), 10);
        }

        if ($wrap.css('margin-bottom')) {
            height_to_reduce += parseInt($wrap.css('margin-bottom').replace('px', ''), 10);
        }

        var res = window_height - height_to_reduce - 130;

        return res > 620 ? res : 620;
    }

    function getCurrentStaffId() {
        return $('.bookme-calendar-employee.active').data('id');
    }

    // settings for fullcalendar.
    var settings = {
        // General Display.
        header: {
            left: views,
            center: 'title',
            right: 'prev today next'
        },
        height: heightFC(),
        // Views.
        defaultView: lastView,
        scrollTime: firstHour + ':00:00',
        views: {
            agendaWeek: {
                columnFormat: 'ddd, D'
            },
            multiStaffDay: {
                staffMembers: staffMembers
            }
        },
        isRTL: Bookme.is_rtl,
        firstDay: Bookme.startOfWeek,
        allDayText: Bookme.allDay,
        buttonText: {
            today: Bookme.today,
            month: Bookme.month,
            week: Bookme.week,
            day: Bookme.day
        },
        axisFormat: Bookme.mjsTimeFormat,
        slotDuration: Bookme.slotDuration,
        // Text/Time Customization.
        timeFormat: Bookme.mjsTimeFormat,
        monthNames: Bookme.calendar.longMonths,
        monthNamesShort: Bookme.calendar.shortMonths,
        dayNames: Bookme.calendar.longDays,
        dayNamesShort: Bookme.calendar.shortDays,
        allDaySlot: false,
        eventBackgroundColor: '#ddd',
        // Agenda Options.
        displayEventEnd: true,
        // Event Dragging & Resizing.
        editable: false,
        // Event Data.
        eventSources: [{
            url: ajaxurl,
            data: {
                action: 'bookme_get_bookings_for_calendar',
                csrf_token: Bookme.csrf_token,
                staff_id: function () {
                    return getCurrentStaffId()
                }
            }
        }],
        viewRender: function (view, element) {
            setCookie('bookme_cal_view', view.type);
        },
        // Clicking & Hovering.
        dayClick: function (date, jsEvent, view) {
            var staff_id = getCurrentStaffId();

            show_booking_panel(
                null,
                staff_id,
                date,
                function (event) {
                    if (event == 'refresh') {
                        $fullCalendar.fullCalendar('refetchEvents');
                    } else {
                        if (staff_id == event.staffId || staff_id == 0) {
                            if (event.id) {
                                // Create event in calendar.
                                $fullCalendar.fullCalendar('renderEvent', event);
                            } else {
                                $fullCalendar.fullCalendar('refetchEvents');
                            }
                        } else {
                            // Switch to the event owner tab.
                            $('.bookme-calendar-employee[data-id=' + event.staffId + ']').click();
                        }
                    }
                }
            );
        },
        // Event Rendering.
        eventRender: function (calEvent, $event, view) {
            var $tpl = $(
                '<div class="fc-event-data">' +
                '<div class="fc-content">' +
                '   <div class="fc-time">' +
                '       <span>' + calEvent.start_time + ' - ' + calEvent.end_time + '</span>' +
                '   </div>' +
                '   <div class="fc-customer"></div>' +
                '   <div class="fc-title d-flex align-items-center">' +
                '       <div class="service-color-box sm-color-box m-l-0" style="background-color: ' + calEvent.service_color + '"></div>' +
                '       <span>' + calEvent.service_name + '</span>' +
                '   </div>' +
                '   <div class="d-flex align-items-center fc-employee">' +
                '       <img class="m-r-5 rounded" src="' + calEvent.staff_photo + '" width="18" height="18">' +
                '       <span class="flex-grow-1">' + calEvent.staff_name + '</span>' +
                '   </div>' +
                '</div>' +
                '<div class="bm-tooltip">' +
                '   <div class="fc-time">' +
                '       <span>' + calEvent.start_time + ' - ' + calEvent.end_time + '</span>' +
                '   </div>' +
                '   <div class="fc-customer my-2"></div>' +
                '   <div class="fc-title d-flex align-items-center my-2">' +
                '       <div class="service-color-box sm-color-box m-l-0" style="background-color: ' + calEvent.service_color + '"></div>' +
                '       <span>' + calEvent.service_name + '</span>' +
                '   </div>' +
                '   <div class="d-flex align-items-center fc-employee">' +
                '       <img class="m-r-5 rounded" src="' + calEvent.staff_photo + '" width="18" height="18">' +
                '       <span class="flex-grow-1">' + calEvent.staff_name + '</span>' +
                '   </div>' +
                '</div>' +
                '</div>'
                ),
                $customer = $tpl.find('.fc-customer'),
                $employee = $tpl.find('.fc-employee');

            if (calEvent.clients) {
                $customer.html('<strong>' + calEvent.clients + '</strong>');
            } else {
                $customer.html('<strong>' + calEvent.client_name + '</strong>');
                $customer.append('<div>' + calEvent.client_email + '</div>');
                $customer.append('<div>' + calEvent.client_phone + '</div>');
            }

            switch (calEvent.status) {
                case 'approved':
                    $employee.append('<span class="booking-status-icon approved" title="' + calEvent.status_title + '"><i class="icon-feather-check"></i></span>');
                    break;
                case 'pending':
                    $employee.append('<span class="booking-status-icon pending" title="' + calEvent.status_title + '"><i class="icon-feather-clock"></i></span>');
                    break;
                case 'cancelled':
                    $employee.append('<span class="booking-status-icon cancelled" title="' + calEvent.status_title + '"><i class="icon-feather-x"></i></span>');
                    break;
                case 'rejected':
                    $employee.append('<span class="booking-status-icon rejected" title="' + calEvent.status_title + '"><i class="icon-feather-x-circle"></i></span>');
                    break;
            }

            $tpl.find('.bm-tooltip .fc-time')
                .append(
                    $('<a href="javascript:void(0)" class="fc-icon icon-feather-trash-2"></a>')
                        .attr('title', Bookme.delete)
                        .on('click', function (e) {
                            e.stopPropagation();
                            e.preventDefault();

                            $deleteDialog.data('calEvent', calEvent).modal('show');
                        })
                );

            $event.html($tpl);
            $event.css('background-color', hex_to_rgb(calEvent.service_color, .3))
        },
        eventClick: function (calEvent, jsEvent, view) {
            var visible_staff_id = calEvent.staffId;

            show_booking_panel(
                calEvent.id,
                null,
                null,
                function (event) {
                    if (event == 'refresh') {
                        $fullCalendar.fullCalendar('refetchEvents');
                    } else {
                        if (visible_staff_id == event.staffId || visible_staff_id == 0) {
                            // Update event in calendar.
                            $.extend(calEvent, event);
                            $fullCalendar.fullCalendar('updateEvent', calEvent);
                        } else {
                            // Switch to the event owner tab.
                            $('.bookme-calendar-employee[data-id=' + event.staffId + ']').click();
                        }
                    }
                }
            );

        },
        loading: function (isLoading) {
            if (isLoading) {
                $('.bm-calendar-loading').show();
            }
        },
        eventAfterAllRender: function () {
            $('.bm-calendar-loading').hide();
        }
    };

    // Init fullcalendar
    $fullCalendar.fullCalendar(settings);

    var $fcDatePicker = $('<input type=hidden />');

    $('.fc-toolbar .fc-center h2', $fullCalendar).before($fcDatePicker).on('click', function () {
        $fcDatePicker.datepicker('setDate', $fullCalendar.fullCalendar('getDate').toDate()).datepicker('show');
    });

    // Init date picker for fast navigation in FullCalendar.
    $fcDatePicker.datepicker({
        dayNamesMin: settings.dayNamesShort,
        monthNames: settings.monthNames,
        monthNamesShort: settings.monthNamesShort,
        firstDay: settings.firstDay,
        beforeShow: function (input, inst) {
            inst.dpDiv.queue(function () {
                inst.dpDiv.css({marginTop: '35px', 'font-size': '13.5px'});
                inst.dpDiv.dequeue();
            });
        },
        onSelect: function (dateText, inst) {
            var d = new Date(dateText);
            $fullCalendar.fullCalendar('gotoDate', d);
            if ($fullCalendar.fullCalendar('getView').type != 'agendaDay') {
                $fullCalendar.find('.fc-day').removeClass('bookme-calendar-day-active');
                $fullCalendar.find('.fc-day[data-date="' + moment(d).format('YYYY-MM-DD') + '"]').addClass('bookme-calendar-day-active');
            }
        },
        onClose: function (dateText, inst) {
            inst.dpDiv.queue(function () {
                inst.dpDiv.css({marginTop: '0'});
                inst.dpDiv.dequeue();
            });
        }
    });

    $(window).on('resize', function () {
        $fullCalendar.fullCalendar('option', 'height', heightFC());
    });

    // Click on tabs.
    $tabs.on('click', function (e) {
        e.preventDefault();
        $tabs.removeClass('active');
        $(this).addClass('active');

        $fullCalendar.fullCalendar('refetchEvents');
    });

    // new booking
    $(".bm-new-booking").on('click', function () {
        var staff_id = getCurrentStaffId();

        show_booking_panel(
            null,
            staff_id,
            moment(),
            function (event) {
                if (event == 'refresh') {
                    $fullCalendar.fullCalendar('refetchEvents');
                } else {
                    if (staff_id == event.staffId || staff_id == 0) {
                        if (event.id) {
                            // Create event in calendar.
                            $fullCalendar.fullCalendar('renderEvent', event);
                        } else {
                            $fullCalendar.fullCalendar('refetchEvents');
                        }
                    } else {
                        // Switch to the event owner tab.
                        $('.bookme-calendar-employee[data-id=' + event.staffId + ']').click();
                    }
                }
            }
        );
    });

    // delete dialog
    $("#bm-delete-notify").on('change', function () {
        $('#bm-reason-wrapper').toggle();
    });
    if ($deleteDialog.data('events') == undefined) {
        $deleteDialog.on('click', '.bm-booking-delete', function (e) {
            var calEvent = $deleteDialog.data('calEvent'),
                $this = $(this);

            $this.addClass('bookme-loader').prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'bookme_delete_booking',
                    'csrf_token':Bookme.csrf_token,
                    'id': calEvent.id,
                    'notify': $('#bm-delete-notify').prop('checked') ? 1 : 0,
                    'reason': $('#bm-cancel-reason').val()
                },
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    $this.removeClass('bookme-loader').prop('disabled', false);
                    $fullCalendar.fullCalendar('removeEvents', calEvent.id);
                    $deleteDialog.modal('hide');
                }
            });
        });
    }

    /*
    * Convert hex color code to rgb
    * @param hex
    * @param alpha
    */
    function hex_to_rgb(hex, alpha) {
        hex = hex || '#fff';
        var r = parseInt(hex.slice(1, 3), 16),
            g = parseInt(hex.slice(3, 5), 16),
            b = parseInt(hex.slice(5, 7), 16);

        if (alpha) {
            return "rgba(" + r + ", " + g + ", " + b + ", " + alpha + ")";
        } else {
            return "rgb(" + r + ", " + g + ", " + b + ")";
        }
    }

    /**
     * Set cookie.
     *
     * @param key
     * @param value
     */
    function setCookie(key, value) {
        var expires = new Date();
        expires.setTime(expires.getTime() + 86400000); // 60 × 60 × 24 × 1000
        document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
    }

    /**
     * Get cookie.
     *
     * @param key
     * @return {*}
     */
    function getCookie(key) {
        var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
        return keyValue ? keyValue[2] : null;
    }

});