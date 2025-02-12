(function ($) {
    window.bookmeCustomerBookings = function (options) {
        $('.bookme-cancel-booking').on('click', function (e) {
            $(this).addClass('bookme-loader').prop('disabled',true);
        });

        $('.bookme-show-old-bookings').on('click', function (e) {
            e.preventDefault();
            var $button = $(this),
                $table = $button.prevAll('table.bookme-customer-bookings-table');

            $button.addClass('bookme-loader').prop('disabled',true);
            $.get(
                options.ajaxurl,
                {
                    action: 'bookme_get_old_bookings',
                    csrf_token: BookmeCB.csrf_token,
                    columns: $table.data('columns'),
                    custom_fields: $table.data('custom_fields'),
                    page: $table.data('page') + 1
                },
                function () {},
                'json'
            ).done(function (result) {
                $button.removeClass('bookme-loader').prop('disabled',false);
                if (!result.data.more) {
                    $button.remove();
                }
                if (result.data.html) {
                    $table.find('tr.bookme-no-bookings').remove();
                    $(result.data.html).hide().appendTo($table).show('slow');
                    $table.data('page', $table.data('page') + 1);
                }
            });
        });
    };
})(jQuery);