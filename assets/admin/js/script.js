(function ($) {
    "use strict";

    var $wrapper = $('.bookme-page-wrapper');

    $(document).on('ready', function() {
        // collapse the wp menu
        if ( !$(document.body).hasClass('folded') ) {
            $(document.body).addClass('folded');
        }

        // load google font
        var bm_load_css = function (id, url) {
            if (!document.getElementById('')) {
                var l = document.getElementsByTagName("head")[0], o = document.createElement("link");
                o.id = id, o.rel = "stylesheet", o.type = "text/css", o.href = url, l.appendChild(o)
            }
        };
        bm_load_css("bookme-google-font","//fonts.googleapis.com/css?family=Nunito:400,400i,600,600i,700,700i,800,800i&display=swap");
    });


    $(".mobile-toggle").click(function () {
        $(".nav-menus").toggleClass("open");
    });

    // Button ripple effect
    $('.bm-ripple-effect, .bm-ripple-effect-dark').on('click', function (e) {
        var rippleDiv = $('<span class="bm-ripple-overlay">'), rippleOffset = $(this).offset(),
            rippleY = e.pageY - rippleOffset.top, rippleX = e.pageX - rippleOffset.left;
        rippleDiv.css({
            top: rippleY - (rippleDiv.height() / 2),
            left: rippleX - (rippleDiv.width() / 2)
        }).appendTo($(this));
        window.setTimeout(function () {
            rippleDiv.remove();
        }, 800);
    });

    // tooltip
    tippy('body', {
        target: '[data-tippy-placement]',
        delay: 100,
        arrow: true,
        arrowType: 'sharp',
        size: 'regular',
        duration: 200,
        animation: 'shift-away',
        animateFill: true,
        theme: 'dark',
        interactive: true,
        distance: 10
    });

    // check all
    var $action_btn = $(".site-action", $wrapper);
    $wrapper.on('change', '#bm-checkbox-all', function () {
        $('.bm-check').prop('checked',$(this).is(":checked")).trigger('change');
    })

    // action button
    .on('change', '.bm-check', function () {
        $('.bm-check:checked').length > 0 ? $action_btn.addClass('active') : $action_btn.removeClass('active');

        $('#bm-checkbox-all').prop(
            'checked',
            $('.bm-check:not(:checked)').length == 0
        );
    });

    $action_btn.find('.back-icon').on('click', function () {
        $action_btn.removeClass('active');
    });

    $(document).on('blur','.bookme-capacity-min, .bookme-capacity-max', function () {
        var $parent = $(this).closest('.row'),
            $min = $parent.find('.bookme-capacity-min'),
            $max = $parent.find('.bookme-capacity-max'),
            cap_min = parseInt($min.val()),
            cap_max = parseInt($max.val());

        // check value is not less than 1
        if(cap_min < 1)
            $min.val(1);
        if(cap_max < 1)
            $max.val(1);

        // check capacity max is greater than capacity min
        if (cap_min > cap_max)
            $max.val($min.val());
    });

    // copy shortcode
    $('.bm-shortcode-box button').on('click',function () {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(this).data('code')).select();
        document.execCommand("copy");
        $temp.remove();
    });

    // Hide empty columns in datatable
    $(document).on('init.dt', function(e, dtSettings) {
        if ( e.namespace !== 'dt' )
            return;

        var options = dtSettings.oInit.hideEmptyCols;

        if ($.isArray(options) || options === true) {
            var config     = $.isPlainObject(options) ? options : {},
                api        = new $.fn.dataTable.Api( dtSettings );

            var emptyCount = 0;

            api.columns().every( function () {
                // Check if were only hiding specific columns
                if($.isArray(options) && $.inArray(api.column(this.index()).dataSrc(), options) === -1)
                    return;

                var data = this.data();

                for (var i = 0; i < data.toArray().length; i++)
                    if (data.toArray()[i] === null || data.toArray()[i].length === 0)
                        emptyCount++;


                // If the # of empty is the same as the length, then no values in col were found
                if(emptyCount === data.toArray().length)
                    api.column( this.index() ).visible( false );

                emptyCount = 0;
            } );
        }
    });
})(jQuery);

function toggleFullScreen() {
    if ((document.fullScreenElement) ||
        (!document.mozFullScreen && !document.webkitIsFullScreen)) {
        if (document.documentElement.requestFullScreen) {
            document.documentElement.requestFullScreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullScreen) {
            document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        }
    }
}

function bm_alert(message, type) {
    if (message) {

        if (typeof type == 'undefined')
            type = 'success';

        var $container = jQuery('.bm-alert-area');
        if ($container.length == 0) {
            $container = jQuery('<div class="bm-alert-area"></div>').appendTo('body');
        }

        var class_name = 'bm-alert-' + type,
            alert_type = type == 'success' ? BookmeScript.success : BookmeScript.error;

        var $alert = jQuery('<div role="alert" class="alert">' +
            '            <div class="d-flex">\n' +
            '                <i class="bm-alert-icon ' + class_name + '"></i>' +
            '                <div class="bm-alert-text">' +
            '                    <h2 class="bm-alert-title">' + alert_type + '</h2>' +
            '                    <p class="bm-alert-message">' + message + '</p>' +
            '                    <div class="close">&times;</div>' +
            '                </div>' +
            '            </div>' +
            '        </div>');
        $alert.appendTo($container).fadeIn().css('transform', 'translate3d(0%, 0px, 0px)');

        if (type == 'success') {
            setTimeout(function () {
                remove_alert($alert);
            }, 5000);
        }
        $alert.find('.close').on('click', function (e) {
            e.preventDefault();
            remove_alert();
        });

        function remove_alert() {
            $alert.css('transform', jQuery('html').attr('dir') == 'rtl' ? 'translate3d(-100%, 0px, 0px)' : 'translate3d(100%, 0px, 0px)').fadeOut(200, function () {
                $alert.remove();
            });
        }
    }
}

function bm_init_color_picker(container){
    var $element = container + ' .bm-color-picker';
    var $input = jQuery($element).siblings('.color-input');
    var picker = Pickr.create({
        container: container,
        el: $element,
        theme: 'monolith',
        comparison: false,
        closeOnScroll: true,
        position: 'bottom-start',
        default: $input.val() || '#333333',
        components: {
            preview: false,
            opacity: false,
            hue: true,
            interaction: {
                input: true
            }
        }
    });
    picker.on('change', function(color, instance)  {
        $input.val(color.toHEXA().toString()).trigger('change');
    });
}

function bookme_print_cal(container, year) {
    container.html('');

    var date = new Date(),
        cal = "";
    if (year <= 200) {
        year += 1900;
    }

    var months = Bookme.months,
        weekdays = Bookme.days,
        start_of_week = parseInt(Bookme.start_of_week),
        html = '';

    html += '<div class="row row-sm">';
    for (var month = 0; month < 12; month++) {
        var fd = date;
        fd.setDate(1);
        if (fd.getDate() == 2) {
            fd.setDate(0);
        }
        fd.setMonth(month);
        fd.setYear(year); // first day of month
        var ld              = new Date( new Date( new Date( fd.getTime() ).setMonth( fd.getMonth() + 1 ) ).setDate(0) ); // last day of month
        var ldlm            = new Date( new Date( fd.getTime() ).setDate(0) );  // last day of previous month
        var fdnm            = new Date( new Date( new Date( fd.getTime() ).setMonth( fd.getMonth() + 1 ) ).setDate(1) ); // first day of next month

        cal = '';
        cal += '<div class="col-sm-6"><table class="bookme-dayoff-cal"><tbody><tr><th colspan="7"><strong>' + months[month] + '</strong></th></tr>';
        cal += '<tr class="bookme-dayoff-cal-weeks">';
        for (var ds = start_of_week; ds < 7; ds++) {
            cal += '<th>' + weekdays[ds] + '</th>';
        }
        for (var ds = 0; ds < start_of_week ; ds++) {
            cal += '<th>' + weekdays[ds] + '</th>';
        }
        cal += '</tr>';

        var week = 0;

        // if month not start on the first day of week, render previous month days
        if (fd.getDay() != start_of_week) {
            cal += '<tr>';
            // get days from the start of week to the start of current month
            var diff = fd.getDay() < start_of_week ? fd.getDay() + 7 - start_of_week : Math.abs(fd.getDay() - start_of_week);
            for (var d = (ldlm.getDate() - diff + 1); d <= ldlm.getDate(); d++) {
                cal += '<td class="cal_days_bef_aft">' + d  + '</td>';
                week++;
            }
        }

        // renders days of the current month
        for (i = 1; i <= ld.getDate(); i++) {
            if (week == 0) {
                cal += '<tr>';
            }
            var cur_date = i + '-' + (month + 1) + '-' + year,
                title = i + ', '+ months[month];

            cal += '<td id="dayoff_date_' + cur_date + '" class="bookme-cal-popover" title="' + title + '" data-date="' + cur_date + '">' + i + '</td>';

            week++;
            if (week == 7) {
                cal += '</tr>';
                week = 0;
            }
        }

        // if month not end at end of week
        if ( (start_of_week && ld.getDay() != start_of_week - 1) || (!start_of_week && ld.getDay() != 6) ) {
            // get days from end of the month to end of current week
            var diff = fdnm.getDay() >= start_of_week ? 6 - fdnm.getDay() + start_of_week : 6 - (fdnm.getDay() - start_of_week + 7);
            for (var d = 1; d <= diff + 1; d++) {
                cal += '<td class="cal_days_bef_aft re">' + d + '</td>';
                week++;
                if (week == 7) {
                    cal += '</tr>';
                    week = 0;
                }
            }
        }

        cal += '</tbody></table></div>';
        html += cal;
    }
    html += '</div>';
    jQuery('#bookme_dayoff_nav_year').val(year);
    container.append(html);

    bm_draw_holidays(container, year);
}

function bm_draw_holidays(container, year) {
    container.find('.bookme-dayoff').removeClass('bookme-dayoff').data('id', null);
    container.find('.bookme-dayoff-repeat').removeClass('bookme-dayoff-repeat');
    var days_off = container.data('holidays');
    for (var i in days_off) {
        if (days_off.hasOwnProperty(i)) {
            container.find(bm_get_holiday_selector(days_off[i]))
                .addClass('bookme-dayoff')
                .addClass(days_off[i].hasOwnProperty('y') ? '' : 'bookme-dayoff-repeat')
                .data('id', i);
        }
    }
}

function bm_get_holiday_selector(day) {
    return 'td[id^=dayoff_date_' + day.d + '-' + day.m + '-' + (day.hasOwnProperty('y') ? (day.y + ']') : ']');
}