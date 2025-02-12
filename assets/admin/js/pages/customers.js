jQuery(function ($) {

    var $customers_table = $('#bm-customer-table'),
        $customers_total = $('.bm-customer-count'),
        $add_button = $('.bm-add-customer'),
        $customers_length = $('.bm-customers-length'),
        $export_button = $('.bm-export-customers'),
        $delete_button = $('#bm-delete-button');

    var dt = $customers_table.DataTable({
        order: [[0, 'desc']],
        info: false,
        searching: false,
        lengthChange: false,
        pageLength: 25,
        pagingType: 'numbers',
        processing: true,
        responsive: true,
        serverSide: true,
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, {
                    action: 'bookme_get_customers',
                    csrf_token: Bookme.csrf_token
                });
            }
        },
        columns: [
            {data: 'id'},
            {data: 'full_name', render: $.fn.dataTable.render.text()},
            {data: 'email', render: $.fn.dataTable.render.text()},
            {data: 'phone', render: $.fn.dataTable.render.text()},
            {data: 'wp_user', render: $.fn.dataTable.render.text()},
            {data: 'notes', render: $.fn.dataTable.render.text()},
            {data: 'last_appointment'},
            {data: 'total_appointments'},
            {data: 'payments'},
            {
                responsivePriority: 1,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" data-tippy-placement="top" title="' + Bookme.edit + '" class="btn-icon"><i class="icon-feather-edit"></i></button>';
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
        },
        drawCallback: function( settings ) {
            // update customer count
            $customers_total.text(settings.json.recordsTotal);
        }
    });

    /**
     * Edit customer.
     */
    $customers_table.on('click', 'button', function () {
        var row = dt.row($(this).closest('td'));
        show_customer_dialog(row.data(), after_customer_save);
    });

    /**
     * New customer.
     */
    $add_button.on('click', function () {
        show_customer_dialog(null, after_customer_save);
    });

    /**
     * Delete customers.
     */
    $delete_button.on('click', function () {
        if (confirm(Bookme.are_you_sure)) {
            var data = [],
                $this = $(this),
                $checkboxes = $customers_table.find('tbody input:checked');

            $checkboxes.each(function () {
                data.push(this.value);
            });

            $this.addClass('bookme-loader').prop('disabled',true);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookme_delete_customers',
                    csrf_token: Bookme.csrf_token,
                    ids: data
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        dt.ajax.reload(null, false);
                    } else {
                        bm_alert(response.data.message, 'error');
                    }
                    $this.removeClass('bookme-loader').prop('disabled',false);
                    // hide action button
                    $(".site-action").removeClass('active');
                }
            });
        }
    });

    /*
    * Callback after save customer
    */
    function after_customer_save () {
        dt.ajax.reload(null, false);
    }

    /*
    * Export Customers
    */
    $customers_length.on('change', function () {
        dt.page.len($(this).val()).ajax.reload(null, false);
    });

    /*
    * Export Customers
    */
    $export_button.on('click', function () {
        var config = {
            autoPrint: false,
            fieldSeparator: ',',
            filename: 'Customers',
            exportOptions: { orthogonal: 'export' }
        };
        $.fn.dataTable.ext.buttons.csvHtml5.action(null, dt, null, $.extend({}, $.fn.dataTable.ext.buttons.csvHtml5, config));
    });
});