jQuery(function ($) {
    var $date_filter = $('#bm-filter-date'),
        $approved_bookings = $('#bm-approved-booking'),
        $pending_bookings = $('#bm-pending-booking'),
        $total_bookings = $('#bm-total-booking'),
        $revenue = $('#bm-revenue'),
        revenue = {
            label: Bookme.revenue,
            fill: true,
            data: [],
            yAxisID: 'y-axis-1',
            backgroundColor: 'rgba(42,65,232,0.08)',
            borderColor: '#2a41e8',
            borderWidth: "3",
            pointRadius: 5,
            pointHoverRadius:5,
            pointHitRadius: 10,
            pointBackgroundColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointBorderWidth: "2"
        },
        total   = {
            label: Bookme.bookings,
            fill: true,
            data: [],
            yAxisID: 'y-axis-2',
            backgroundColor: 'rgba(54, 189, 120, 0.08)',
            borderColor: '#36bd78',
            borderWidth: "3",
            pointRadius: 5,
            pointHoverRadius:5,
            pointHitRadius: 10,
            pointBackgroundColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointBorderWidth: "2"
        };

    Chart.defaults.global.defaultFontFamily = "Nunito";
    Chart.defaults.global.defaultFontColor = '#888';
    Chart.defaults.global.defaultFontSize = '14';
    var chart = Chart.Line(document.getElementById('bm-chart').getContext('2d'), {
        data: {
            labels: [],
            datasets: [revenue, total]
        },
        options: {
            responsive: true,
            hoverMode : 'index',
            stacked   : false,
            title     : {
                display: false
            },
            scales: {
                yAxes: [{
                    type: 'linear',
                    display: true,
                    position: 'left',
                    id: 'y-axis-1',
                    scaleLabel: {
                        labelString: Bookme.revenue + ' ('+ Bookme.currency +')',
                        display: true
                    },
                    gridLines: {
                        borderDash: [6, 10],
                        color: "#d8d8d8",
                        lineWidth: 1,
                    },
                    ticks: {
                        beginAtZero:true
                    }
                }, {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    id: 'y-axis-2',
                    scaleLabel: {
                        labelString: Bookme.bookings,
                        display: true
                    },
                    gridLines: {
                        borderDash: [6, 10],
                        color: "#d8d8d8",
                        lineWidth: 1,
                    },
                    ticks: {
                        beginAtZero:true
                    }
                }],
                xAxes: [{
                    gridLines: {
                        borderDash: [6, 10],
                        color: "#d8d8d8",
                        lineWidth: 1
                    }
                }]
            },
            legend: {
                position: 'bottom'
            },
            tooltips: {
                backgroundColor: '#333',
                titleFontSize: 13,
                titleFontColor: '#fff',
                bodyFontColor: '#fff',
                bodyFontSize: 13,
                displayColors: false,
                xPadding: 10,
                yPadding: 10,
                intersect: false
            }
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

            get_data(start.format(format) + ' - ' + end.format(format));
        }
    );


    get_data(moment().subtract(30, 'days').format('YYYY-MM-DD') + ' - ' + moment().format('YYYY-MM-DD'));

    function get_data(data) {
        $('.bookme-card .card-body').css('opacity','0.5').addClass('bookme-loader bookme-loader-dark');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bookme_get_data_for_dashboard',
                csrf_token: Bookme.csrf_token,
                range: data
            },
            dataType: 'json',
            success: function (response) {
                $approved_bookings.html(response.data.approved);
                $pending_bookings.html(response.data.pending);
                $total_bookings.html(response.data.total);
                $revenue.html(response.data.revenue);

                revenue.data = [];
                total.data = [];
                $.each(response.data.days,function (date, item) {
                    revenue.data.push(item.revenue);
                    total.data.push(item.total);
                });

                chart.data.labels = response.data.labels;
                chart.update();

                $('.bookme-card .card-body').css('opacity','1').removeClass('bookme-loader bookme-loader-dark');
            }
        });
    }
});