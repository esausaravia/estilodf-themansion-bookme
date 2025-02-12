jQuery(function ($) {
    var $payment_panel = $('#bookme-payment-details-dialog'),
        $loader = $payment_panel.find('.bookme-loading'),
        $panel_body = $payment_panel.find('.payment-dialog-body'),
        $print_button = $payment_panel.find('.bm-payment-print');

    $(document).on('click', '.bookme-payment-details-show', function (e) {
        e.preventDefault();
        show_payment_panel(this);
    });

    function show_payment_panel($button, payment_id) {
        bm_show_sidepanel($payment_panel);
        if (payment_id === undefined) {
            payment_id = $($button).attr('data-payment_id');
        }
        $loader.show();
        $panel_body.hide();

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'bookme_get_payment_details',
                payment_id: payment_id,
                csrf_token: Bookme.csrf_token
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $loader.hide();
                    $panel_body.html(response.data.html).show();

                    $panel_body.find('#bookme-complete-payment').on('click', function () {
                        var $this = $(this);
                        $this.addClass('bookme-loader').prop('disabled', true);
                        $.ajax({
                            url: ajaxurl,
                            data: {
                                action: 'bookme_complete_payment',
                                payment_id: payment_id,
                                csrf_token: Bookme.csrf_token
                            },
                            dataType: 'json',
                            type: 'POST',
                            success: function (response) {
                                if (response.success) {
                                    show_payment_panel($button, payment_id);

                                    // Reload DataTable.
                                    var $table = $($button).closest('table.dataTable');
                                    if ($table.length) {
                                        $table.DataTable().ajax.reload();
                                    }
                                }
                            }
                        });
                    });
                }
            }
        });
    }

    $payment_panel.on('sidePanel.hide', function () {
        $('body').css('overflow-y', '');
        $('.slidePanel-wrapper').remove();
    });

    $print_button.on('click', function () {
        var w = window.open();
        var content = $panel_body.html();
        content += '<style>.table,td,th{border: 1px solid #eee;border-collapse: collapse;text-align: left;padding: 10px;width: 500px;} .table-responsive{margin: 0 auto; margin-bottom: 16.5px; border: 1px solid #e0e0e0; width: fit-content; text-align: center;} #bookme-complete-payment{display:none}</style>';
        w.document.write(content);
        w.document.close();
        w.print();
    });
});