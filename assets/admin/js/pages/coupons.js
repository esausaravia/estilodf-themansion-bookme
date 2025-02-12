jQuery(function ($) {
    var $coupons_table = $('#bm-coupon-table'),
        $coupon_panel = $('#bm-coupon-sidepanel'),
        $coupon_new_title = $('#bm-add-coupon-title'),
        $coupon_edit_title = $('#bm-edit-coupon-title'),
        $coupon_code = $('#bm-code'),
        $coupon_discount = $('#bm-discount'),
        $coupon_deduction = $('#bm-deduction'),
        $coupon_usage_limit = $('#bm-usage-limit'),
        $save_button = $('#ajax-save-coupon'),
        $add_button = $('.bm-add-coupon'),
        $delete_button = $('#bm-delete-button'),
        $service_list = $('.bm-services'),
        row;

    $service_list.multiselect({
        texts: {
            placeholder: $service_list.data('placeholder'), // text to use in dummy input
            selectedOptions: ' ' + $service_list.data('selected'),      // selected suffix text
            selectAll: $service_list.data('selectall'),     // select all text
            unselectAll: $service_list.data('unselectall'),   // unselect all text
            noneSelected: $service_list.data('nothing'),   // None selected text
            allSelected: $service_list.data('allselected')
        },
        showCheckbox: false,  // display the checkbox to the user
        selectAll: true, // add select all option
        minHeight: 20,
        maxPlaceholderOpts: 1
    });

    /**
     * Init DataTables.
     */
    var dt = $coupons_table.DataTable({
        order: [[0, "asc"]],
        paging: false,
        info: false,
        searching: false,
        processing: true,
        responsive: true,
        ajax: {
            url: ajaxurl,
            data: {action: 'bookme_get_coupons', csrf_token: Bookme.csrf_token}
        },
        columns: [
            {data: "code"},
            {data: "discount"},
            {data: "deduction"},
            {
                data: 'service_ids',
                render: function (data, type, row, meta) {
                    if (data.length == 0) {
                        return Bookme.nothing_selected;
                    } else if (data.length == 1) {
                        return "<div class='badge badge-primary'>"+Bookme.collection[data[0]].title+"</div>";
                    } else {
                        if (data.length == Object.keys(Bookme.collection).length) {
                            return Bookme.all_selected;
                        } else {
                            var rest = data.length - 1;
                            var services = data.slice(0, 1);
                            var tpl = '';
                            $.each(services, function (id, index) {
                                tpl += "<div class='badge badge-primary'>"+Bookme.collection[index].title+"</div>";
                            });
                            tpl += "<div class='badge badge-primary'>+"+rest+"</div>";
                            return tpl;
                        }
                    }
                }
            },
            {data: "usage_limit"},
            {data: "used"},
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
        }
    });

    /**
     * Edit coupon.
     */
    $coupons_table.on('click', 'button', function () {
        row = dt.row($(this).closest('td'));
        show_coupon_panel();
    });

    /**
     * New coupon.
     */
    $add_button.on('click', function () {
        row = null;
        show_coupon_panel();
    });

    /**
     * Save coupon.
     */
    $save_button.on('click', function (e) {
        e.preventDefault();
        var $form = $coupon_panel.find('form');
        var data = $form.serializeArray();
        data.push({name: 'action', value: 'bookme_save_coupon'});
        if (row) {
            data.push({name: 'id', value: row.data().id});
        }
        var $this = $(this);
        $this.addClass('bookme-loader').prop('disabled',true);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    if (row) {
                        row.data(response.data).draw();
                    } else {
                        dt.row.add(response.data).draw();
                    }
                    bm_hide_sidepanel($coupon_panel);
                } else {
                    bm_alert(response.data.message,'error');
                }
                $this.removeClass('bookme-loader').prop('disabled',false);
            }
        });
    });

    /**
     * Delete coupons.
     */
    $delete_button.on('click', function () {
        if (confirm(Bookme.are_you_sure)) {
            var $this = $(this);
            var data = [];
            var $checkboxes = $coupons_table.find('tbody input:checked');
            $checkboxes.each(function () {
                data.push(this.value);
            });

            $this.addClass('bookme-loader').prop('disabled',true);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookme_delete_coupons',
                    csrf_token: Bookme.csrf_token,
                    ids: data
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        dt.rows($checkboxes.closest('td')).remove().draw();
                    } else {
                        bm_alert(response.data.message,'error');
                    }
                    $this.removeClass('bookme-loader').prop('disabled',false);
                    // hide action button
                    $(".site-action").removeClass('active');
                }
            });
        }
    });

    function show_coupon_panel() {
        var data = {};
        if (row) {
            data = row.data();
            $coupon_code.val(data.code);
            $coupon_discount.val(data.discount);
            $coupon_deduction.val(data.deduction);
            $coupon_usage_limit.val(data.usage_limit);
            $coupon_edit_title.show();
            $coupon_new_title.hide();
        } else {
            $coupon_code.val('');
            $coupon_discount.val('0');
            $coupon_deduction.val('0');
            $coupon_usage_limit.val('1');
            $coupon_edit_title.hide();
            $coupon_new_title.show();
        }
        $service_list.html('');
        $.each(Bookme.services, function (category, services) {
            var tpl = '<optgroup label="' + category + '">';
            $.each(services, function (id, service) {
                tpl += '<option value="' + service.id + '"' + ($.inArray(service.id, data.service_ids) != -1 ? ' selected' : '') + '>' + service.title + '</option>';
            });
            tpl += '</optgroup>';
            $service_list.append(tpl);
        });
        $service_list.multiselect('reload');

        bm_show_sidepanel($coupon_panel);
    }
});
