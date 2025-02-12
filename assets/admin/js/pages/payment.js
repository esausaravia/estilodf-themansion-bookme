jQuery(function ($) {
    var $payments_table = $('#bm-payment-table'),
        $date_filter = $('#bm-filter-date'),
        $type_filter = $('#bm-filter-type'),
        $staff_filter = $('#bm-filter-employee'),
        $service_filter = $('#bm-filter-service'),
        $delete_button = $('#bm-delete-button');

    $('.bm-select2')
        .val(null)
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

    /**
     * Init DataTables.
     */
    var dt = $payments_table.DataTable({
        order: [[0, 'asc']],
        paging: false,
        info: false,
        searching: false,
        processing: true,
        responsive: true,
        serverSide: true,
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, {
                    action: 'bookme_get_payments',
                    csrf_token: Bookme.csrf_token,
                    filter: {
                        created: $date_filter.data('date'),
                        type: $type_filter.val(),
                        staff: $staff_filter.val(),
                        service: $service_filter.val()
                    }
                });
            }
        },
        columns: [
            {
                data: 'created',
                render: function (data, type, row, meta) {
                    return '<strong>' + row.created_time + '</strong><br>' +
                        row.created_date;
                }
            },
            {data: 'customer', render: $.fn.dataTable.render.text()},
            {data: 'employee'},
            {data: 'service'},
            {
                data: 'start_date',
                render: function (data, type, row, meta) {
                    return '<strong>' + row.start_time + '</strong><br>' +
                        row.start_date;
                }
            },
            {data: 'amount'},
            {data: 'type'},
            {data: 'status'},
            {
                responsivePriority: 1,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn-icon bookme-payment-details-show" data-payment_id="' + row.id + '" title="'+Bookme.details+'" data-tippy-placement="top"><i class="icon-feather-info"></i></a>';
                }
            },
            {
                responsivePriority: 1,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '<div class="checkbox"><input type="checkbox" id="check_' + row.id + '" value="' + row.id + '" class="bm-check"><label for="check_' + row.id + '"><span class="checkbox-icon"></span></label></div>';
                }
            }
        ],
        language: {
            zeroRecords: Bookme.zeroRecords,
            processing: Bookme.processing
        }
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
    picker_ranges[Bookme.last_7] = [moment().subtract(7, 'days'), moment()];
    picker_ranges[Bookme.last_30] = [moment().subtract(30, 'days'), moment()];
    picker_ranges[Bookme.this_month] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[Bookme.last_month] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

    $date_filter.daterangepicker(
        {
            parentEl: $date_filter.parent(),
            startDate: moment().subtract(30, 'days'), // by default selected is "Last 30 days"
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


    $date_filter.on('apply.daterangepicker', function () {
        dt.ajax.reload();
    });
    $type_filter.on('change', function () {
        dt.ajax.reload();
    });
    $staff_filter.on('change', function () {
        dt.ajax.reload();
    });
    $service_filter.on('change', function () {
        dt.ajax.reload();
    });

    /**
     * Delete payments.
     */
    $delete_button.on('click', function () {
        if (confirm(Bookme.are_you_sure)) {
            var $this = $(this);

            var data = [];
            var $checkboxes = $payments_table.find('tbody input:checked');
            $checkboxes.each(function () {
                data.push(this.value);
            });

            $this.addClass('bookme-loader').prop('disabled', true);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookme_delete_payments',
                    csrf_token: Bookme.csrf_token,
                    data: data
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        dt.rows($checkboxes.closest('td')).remove().draw();
                    } else {
                        bm_alert(response.data.message,'error');
                    }
                    // hide action button
                    $(".site-action").removeClass('active');
                    $this.removeClass('bookme-loader').prop('disabled', false);
                }
            });
        }
    });
});