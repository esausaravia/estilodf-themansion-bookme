jQuery(function ($) {

    var $bookings_table = $('#bm-bookings-table'),
        $id_filter = $('#bm-filter-id'),
        $date_filter = $('#bm-filter-date'),
        $employee_filter = $('#bm-filter-employee'),
        $customer_filter = $('#bm-filter-customer'),
        $service_filter = $('#bm-filter-service'),
        $status_filter = $('#bm-filter-status'),
        $add_button = $('.bm-add-booking'),
        $export_button = $('.bm-export-booking');


    $('.bm-select2').val(null);
    $.each(Bookme.filter, function (field, value) {
        if (value != '') {
            $('#bm-filter-' + field).val(value);
        }
        // check if select has correct values
        if ($('#bm-filter-' + field).prop('type') == 'select-one') {
            if ($('#bm-filter-' + field + ' option[value="' + value + '"]').length == 0) {
                $('#bm-filter-' + field).val(null);
            }
        }
    });

    /**
     * Init DataTables.
     */
    var columns = [
        {data: 'id', responsivePriority: 2},
        {
            data: 'start_date',
            responsivePriority: 2,
            render: function (data, type, row, meta) {
                return type === 'export'
                    ? row.start_time + ' ' + data
                    : '<strong>' + row.start_time + '</strong><br>' + data;
            },
            defaultContent: ''
        },
        {
            data: 'customer.full_name',
            responsivePriority: 2,
            render: function (data, type, row, meta) {
                return type === 'export'
                    ? $.fn.dataTable.render.text().display(data) + ' (' +
                    row.customer.email + ', ' +
                    row.customer.phone+ ')'
                    : '<strong>' + $.fn.dataTable.render.text().display(data) + '</strong><br>' +
                    row.customer.email + '<br>' +
                    row.customer.phone;
            },
            defaultContent: ''
        },
        {
            data: 'staff.name',
            responsivePriority: 2,
            render: function (data, type, row, meta) {
                return type === 'export'
                    ? data
                    : '<div class="d-flex align-items-center">' +
                    '<img class="m-r-5 rounded" src="' + row.staff.photo + '" width="25" height="25">' +
                    '<span>' + data + '</span>' +
                    '</div>';
            },
            defaultContent: ''
        },
        {
            data: 'service.title',
            responsivePriority: 2,
            render: function (data, type, row, meta) {
                return type === 'export'
                    ? data
                    : '<div class="d-flex align-items-center">' +
                    '<div class="service-color-box sm-color-box m-l-0" style="background-color:' + row.service.color + '"></div>' +
                    '<span>' + data + '</span>' +
                    '</div>';
            },
            defaultContent: ''
        },
        {data: 'service.duration', responsivePriority: 2, defaultContent: ''},
        {
            data: 'status',
            responsivePriority: 2,
            render: function (data, type, row, meta) {
                if(type === 'export')
                    return row.status_title

                var icon = '';
                switch (data) {
                    case 'approved':
                        icon = '<span class="booking-status-icon approved m-r-5"><i class="icon-feather-check"></i></span>';
                        break;
                    case 'pending':
                        icon = '<span class="booking-status-icon pending m-r-5"><i class="icon-feather-clock"></i></span>';
                        break;
                    case 'cancelled':
                        icon = '<span class="booking-status-icon cancelled m-r-5"><i class="icon-feather-x"></i></span>';
                        break;
                    case 'rejected':
                        icon = '<span class="booking-status-icon rejected m-r-5"><i class="icon-feather-x-circle"></i></span>';
                        break;
                }
                return '<div class="d-flex align-items-center">' +
                    icon +
                    '<span>' + row.status_title + '</span>' +
                    '</div>';
            },
            defaultContent: ''
        },
        {
            data: 'payment',
            responsivePriority: 2,
            render: function (data, type, row, meta) {
                return type === 'export'
                    ? data
                    : '<a href="#" class="bookme-payment-details-show" data-payment_id="' + row.payment_id + '">' + data + '</a>';
            },
            defaultContent: ''
        },
        {
            data: 'online_meeting',
            responsivePriority: 4,
            render: function (data, type, row, meta) {
                return type === 'export'
                    ? data
                    : (data.length
                        ? '<a class="btn btn-primary btn-sm" href="' +data+ '" target="_blank">' + Bookme.zoom + ' <i class="icon-feather-external-link"></i>' + '</a>'
                        : '');
            },
            defaultContent: ''
        }
    ];
    $.each(Bookme.cf_columns, function (i, cf_id) {
        columns.push({
            data: 'custom_fields.' + cf_id,
            render: $.fn.dataTable.render.text(),
            responsivePriority: 4,
            orderable: false,
            defaultContent: ''
        });
    });
    var dt = $bookings_table.DataTable({
        order: [[1, 'desc']],
        info: false,
        paging: false,
        searching: false,
        processing: true,
        responsive: true,
        serverSide: true,
        hideEmptyCols: ['online_meeting'],
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({action: 'bookme_get_dt_bookings', csrf_token: Bookme.csrf_token}, {
                    filter: {
                        id: $id_filter.val(),
                        date: $date_filter.data('date'),
                        staff: $employee_filter.val(),
                        customer: $customer_filter.val(),
                        service: $service_filter.val(),
                        status: $status_filter.val()
                    }
                }, d);
            }
        },
        columns: columns.concat([
            {
                data: 'id',
                responsivePriority: 1,
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" data-tippy-placement="top" title="' + Bookme.edit + '" class="btn-icon"><i class="icon-feather-edit"></i></button>';
                },
                defaultContent: ''
            },
            {
                data: 'id',
                responsivePriority: 1,
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<div class="checkbox"><input type="checkbox" id="check_' + row.ca_id + '" value="' + row.ca_id + '" class="bm-check"><label for="check_' + row.ca_id + '"><span class="checkbox-icon"></span></label></div>';
                },
                defaultContent: ''
            }
        ]),
        language: {
            zeroRecords: Bookme.zeroRecords,
            processing: Bookme.processing
        }
    });


    $add_button.on('click', function () {
        show_booking_panel(
            null,
            null,
            moment(),
            function (event) {
                dt.ajax.reload();
            }
        )
    });


    $bookings_table.on('click', 'button', function (e) {
        e.preventDefault();
        var data = dt.row($(this).closest('td')).data();
        show_booking_panel(
            data.id,
            null,
            null,
            function (event) {
                dt.ajax.reload();
            }
        )
    });

    // delete dialog
    $("#bm-delete-notify").on('change', function () {
        $('#bm-reason-wrapper').toggle();
    });
    $('.bm-booking-delete').on('click', function () {
        var $this = $(this);
        var data = [];
        var $checkboxes = $bookings_table.find('tbody input:checked');
        $checkboxes.each(function () {
            data.push(this.value);
        });

        $this.addClass('bookme-loader').prop('disabled', true);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bookme_delete_dt_bookings',
                csrf_token: Bookme.csrf_token,
                data: data,
                notify: $('#bm-delete-notify').prop('checked') ? 1 : 0,
                reason: $('#bm-cancel-reason').val()
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    dt.draw(false);
                } else {
                    bm_alert(response.data.message, 'error');
                }
                $this.removeClass('bookme-loader').prop('disabled', false);
                $('#bm-booking-delete-dialog').modal('hide');
                // hide action button
                $(".site-action").removeClass('active');
            }
        });
    });

    /**
     * Init date range picker.
     */
    moment.locale('en', {
        months: Bookme.calendar.longMonths,
        monthsShort: Bookme.calendar.shortMonths,
        weekdays: Bookme.calendar.longDays,
        weekdaysShort: Bookme.calendar.shortDays,
        weekdaysMin: Bookme.calendar.shortDays
    });

    var picker_ranges = {};
    picker_ranges[Bookme.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    picker_ranges[Bookme.today] = [moment(), moment()];
    picker_ranges[Bookme.tomorrow] = [moment().add(1, 'days'), moment().add(1, 'days')];
    picker_ranges[Bookme.last_7] = [moment().subtract(7, 'days'), moment()];
    picker_ranges[Bookme.last_30] = [moment().subtract(30, 'days'), moment()];
    picker_ranges[Bookme.this_month] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[Bookme.next_month] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];

    $date_filter.daterangepicker(
        {
            parentEl: $date_filter.parent(),
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            ranges: picker_ranges,
            locale: {
                applyLabel: Bookme.apply,
                cancelLabel: Bookme.cancel,
                fromLabel: Bookme.from,
                toLabel: Bookme.to,
                customRangeLabel: Bookme.custom_range,
                daysOfWeek: Bookme.calendar.shortDays,
                monthNames: Bookme.calendar.longMonths,
                firstDay: parseInt(Bookme.startOfWeek),
                format: Bookme.mjsDateFormat
            }
        },
        function (start, end) {
            var format = 'YYYY-MM-DD';
            $date_filter
                .attr('title', start.format(Bookme.mjsDateFormat) + ' - ' + end.format(Bookme.mjsDateFormat))
                .data('date', start.format(format) + ' - ' + end.format(format))
                .find('span')
                .html(start.format(Bookme.mjsDateFormat) + ' - ' + end.format(Bookme.mjsDateFormat));
        }
    );

    /**
     * On filters change.
     */
    $('.bm-select2')
        .on('select2:unselecting', function (e) {
            e.preventDefault();
            $(this).val(null).trigger('change');
        })
        .select2({
            width: '100%',
            allowClear: true,
            language: {
                noResults: function () {
                    return Bookme.no_result_found;
                }
            }
        });

    $id_filter.on('keyup', function () {
        dt.ajax.reload();
    });
    $date_filter.on('apply.daterangepicker', function () {
        dt.ajax.reload();
    });
    $employee_filter.on('change', function () {
        dt.ajax.reload();
    });
    $customer_filter.on('change', function () {
        dt.ajax.reload();
    });
    $service_filter.on('change', function () {
        dt.ajax.reload();
    });
    $status_filter.on('change', function () {
        dt.ajax.reload();
    });


    $export_button.on('click', function () {
        var config = {
            autoPrint: false,
            fieldSeparator: ',',
            filename: 'Bookings',
            exportOptions: { orthogonal: 'export' }
        };
        $.fn.dataTable.ext.buttons.csvHtml5.action(null, dt, null, $.extend({}, $.fn.dataTable.ext.buttons.csvHtml5, config));
    });
});