jQuery(function ($) {
    var $no_result = $('.all-services-box .bm-no-result'),
        $add_category = $('.bm-add-category'),
        $categories = $('.bm-categories'),
        $services_wrapper = $('.services-wrapper'),
        update_staff_choice = null;

    // Create category
    $add_category.on('click', function () {
        var $this = $(this);
        $this.addClass('bookme-loader').prop('disabled',true);
        $.post(ajaxurl, {
            action: 'bookme_create_category',
            csrf_token: Bookme.csrf_token
        }, function (response) {
            $this.removeClass('bookme-loader').prop('disabled',false);
            $('.bm-categories-list').append(response.data.html);
            var $new_category = $('.category-item:last');
            $new_category.find('.category-item-edit').click();
        });
    });

    // Categories list
    $categories
    // On category click
        .on('click', '.category-item', function (e) {
            if ($(e.target).is('.bookme-reorder-icon')) return;
            $no_result.hide();
            $('.services-wrapper').html('<div class="bookme-loading"></div>');
            var $this = $(this);
            $.get(ajaxurl, {
                action: 'bookme_get_category_services',
                category_id: $this.data('id'),
                csrf_token: Bookme.csrf_token
            }, function (response) {
                if (response.success) {
                    $('.category-item').not($this).removeClass('active');
                    $this.addClass('active');
                    $('.category-item-title').text($this.text());
                    $services_wrapper.html(response.data);
                    if (response.data.length > 0) {
                        $no_result.hide();
                    } else {
                        $no_result.show();
                    }
                    makeSortable();
                }
            });
        })
        // Edit category
        .on('click', '.category-item-edit', function (e) {
            e.stopPropagation();
            e.preventDefault();
            var $this = $(this).closest('.category-item');
            $this.find('.category-item-name').hide();
            $this.find('input').show().focus();
        })
        // On blur
        .on('blur', 'input', update_category)
        // On enter
        .on('keypress', 'input', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                update_category.apply(this);
            }
        })
        .on('click', 'input', function (e) {
            e.stopPropagation();
        })
        // Delete category
        .on('click', '.category-item-delete', function (e) {
            e.stopPropagation();
            e.preventDefault();
            var $this = $(this);
            // Ask user if he is sure.
            if (confirm(Bookme.are_you_sure)) {
                $this.addClass('bookme-loader bookme-loader-dark').prop('disabled',true);
                var $item = $(this).closest('.category-item');
                var data = {
                    action: 'bookme_delete_category',
                    id: $item.data('id'),
                    csrf_token: Bookme.csrf_token
                };
                $.post(ajaxurl, data, function () {
                    // Remove category item from DOM.
                    $item.remove();
                    if ($item.is('.active')) {
                        $('.category-item-all').click();
                    }
                });
            }
        });

    // category position
    $('.bm-categories-list').sortable({
        axis: 'y',
        handle: '.bookme-reorder-icon',
        update: function (event, ui) {
            var data = [];
            $('.bm-categories-list').children('li').each(function () {
                var position = $(this).data('id');
                data.push(position);
            });
            $.post(ajaxurl,
                {
                    action: 'bookme_category_position',
                    position: data,
                    csrf_token: Bookme.csrf_token
                }
            );
        }
    });

    // Save category.
    function update_category() {
        var $this = $(this),
            $item = $this.closest('.category-item'),
            $edit_icon = $item.find('.category-item-edit'),
            $name = $item.find('.category-item-name'),
            value = $this.val(),
            id = $item.data('id'),
            data = {action: 'bookme_update_category', id: id, name: value, csrf_token: Bookme.csrf_token};

        if($name.text() != value) {
            $edit_icon.addClass('bookme-loader bookme-loader-dark').prop('disabled',true);
            $.post(ajaxurl, data, function () {
                bm_alert(Bookme.category_updated);
                $edit_icon.removeClass('bookme-loader bookme-loader-dark').prop('disabled', false);
            });
        }
        // Show modified category name.
        $name.text(value);
        // Hide input field.
        $item.find('input').hide();
        $item.find('.category-item-name').show();
    }

    // Services delete
    $('#bm-delete-button').on('click', function (e) {
        if (confirm(Bookme.are_you_sure)) {
            var $for_delete = $('.bm-check:checked'),
                data = {action: 'bookme_delete_services', csrf_token: Bookme.csrf_token},
                services = [],
                $rows = [],
                $this = $(this);

            $for_delete.each(function () {
                var row = $(this).parents('tr');
                $rows.push(row);
                services.push(this.value);
            });
            data['service_ids[]'] = services;

            $this.addClass('bookme-loader').prop('disabled',true);
            $.post(ajaxurl, data, function (response) {
                if (response.success) {
                    $this.removeClass('bookme-loader').prop('disabled',false);
                    // hide action button
                    $(".site-action").removeClass('active');
                    // remove rows
                    $.each($rows.reverse(), function (index) {
                        $(this).delay(500 * index).fadeOut(200, function () {
                            $(this).remove();
                        });
                    });
                }
            });
        }
    });

    function makeSortable() {
        if ($('.category-item-all').hasClass('active')) {
            var $services = $('#services-tbody');
            $services.sortable({
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
                    $services.children('tr').each(function () {
                        data.push($(this).data('service-id'));
                    });
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'bookme_update_services_position',
                            position: data,
                            csrf_token: Bookme.csrf_token
                        }
                    });
                }
            });
        } else {
            $('.services-wrapper .bookme-reorder-icon').hide();
        }
    }
    makeSortable();


    var $modal = $('#update-service-dialog');
    $modal
        .on('click', '.bm-yes', function () {
            $modal.modal('hide');
            if ($('#remeber_choice').prop('checked')) {
                update_staff_choice = true;
            }
            submit_service_from($modal.data('input'), true);
        })
        .on('click', '.bm-no', function () {
            $modal.modal('hide');
            if ($('#remeber_choice').prop('checked')) {
                update_staff_choice = false;
            }
            submit_service_from($modal.data('input'), false);
        });

    // call when sidepanel is loaded
    window.bookmeSidePanelLoaded = function ($panel) {
        // initialize color picker
        bm_init_color_picker('.slidePanel-inner');

        var $staff_preference = $panel.find('[name=staff_preference]'),
            $staff_list = $panel.find('.bookme-employees-list'),
            $staff_member = $panel.find('#bookme-employees'),
            $staff_box = $panel.find('.bookme-employees-wrapper');

        $staff_member.multiselect({
            texts: {
                placeholder: $staff_member.data('placeholder'), // text to use in dummy input
                selectedOptions: ' ' + $staff_member.data('selected'),      // selected suffix text
                selectAll: $staff_member.data('selectall'),     // select all text
                unselectAll: $staff_member.data('unselectall'),   // unselect all text
                noneSelected: $staff_member.data('nothing'),   // None selected text
                allSelected: $staff_member.data('allselected')
            },
            showCheckbox: false,  // display the checkbox to the user
            selectAll: true, // add select all option
            minHeight: 20,
            maxPlaceholderOpts: 1
        });

        $staff_preference.on('change', function () {
            /** @see Service::PREFERRED_ORDER */
            if ($(this).val() == 'order' && $staff_list.html() == '') {
                var $staff_ids = $staff_preference.data('default'),
                    $draggable = $('<li><i class="icon-feather-menu bookme-reorder-icon m-r-5" title="' + Bookme.reorder + '"></i><input type="hidden" name="positions[]"></li>');

                $staff_ids.forEach(function (id) {
                    $staff_list.append($draggable.clone().find('input').val(id).end().append(Bookme.employees[id]));
                });
                Object.keys(Bookme.employees).forEach(function (id) {
                    id = parseInt(id);
                    if ($staff_ids.indexOf(id) == -1) {
                        $staff_list.append($draggable.clone().find('input').val(id).end().append(Bookme.employees[id]));
                    }
                });
            }
            $staff_box.toggle($(this).val() == 'order');
        }).trigger('change');

        $panel
            .find('[name=duration]').on('change', function () {
            $panel.find('[name=start_time_info]').closest('.row').toggle($(this).val() >= 86400);
        }).trigger('change');

        $panel
            .find('.ajax-save-service').on('click', function (e) {
            e.preventDefault();
            var $form = $panel.find('form'),
                show_modal = false;
            if (validateForm($form)) {
                if ($form.find('input[name=id]').val()) {
                    if (update_staff_choice === null) {
                        $('.bookme-question', $form).each(function () {
                            if ($(this).data('last_value') != this.value) {
                                show_modal = true;
                            }
                        });
                    }
                }
                if (show_modal) {
                    $modal.data('input', $form).modal('show');
                } else {
                    submit_service_from($form, update_staff_choice);
                }
            }
        });

        $panel
            .find('.ajax-delete-service').on('click', function (e) {
            e.preventDefault();
            if (confirm(Bookme.are_you_sure)) {
                var $this = $(this),
                    id = $panel.find('input[name=id]').val(),
                    data = {action: 'bookme_delete_services', csrf_token: Bookme.csrf_token};

                data['service_ids[]'] = id;

                $this.addClass('bookme-loader').prop('disabled',true);
                $.post(ajaxurl, data, function (response) {
                    $this.removeClass('bookme-loader').prop('disabled',false);
                    if (response.success) {
                        $.slidePanel.hide();
                        $('[data-service-id=' + id + ']').fadeOut(200, function () {
                            $(this).remove();
                        });
                    }
                });
            }
        });

        $panel
            .find('.bookme-question').each(function () {
            $(this).data('last_value', this.value);
        });

        $staff_list.sortable({
            axis: 'y',
            handle: '.bookme-reorder-icon',
            update: function () {
                var $id  = $(this).data('service_id');
                if($id) {
                    var positions = [];
                    $('[name="positions[]"]', $(this)).each(function () {
                        positions.push(this.value);
                    });

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'bookme_update_service_employee_preference_orders',
                            service_id: $id,
                            positions: positions,
                            csrf_token: Bookme.csrf_token
                        }
                    });
                }
            }
        });
    };

    function submit_service_from($form, update_staff) {
        $form.find('input[name=update_staff]').val(update_staff ? 1 : 0);
        var $button = $('button.ajax-save-service'),
            data = $form.serializeArray();
        $button.addClass('bookme-loader').prop('disabled',true);

        $.post(ajaxurl, data, function (response) {
            if (response.success) {
                var $price = $form.find('[name=price]'),
                    $capacity_min = $form.find('[name=capacity_min]'),
                    $capacity_max = $form.find('[name=capacity_max]'),
                    $id = $form.find('[name=id]');
                $price.data('last_value', $price.val());
                $capacity_min.data('last_value', $capacity_min.val());
                $capacity_max.data('last_value', $capacity_max.val());
                $id.val(response.data.service_id);
                bm_alert(Bookme.service_updated);
                $('.category-item.active').trigger('click');
            }
        }, 'json').always(function () {
            $button.removeClass('bookme-loader').prop('disabled',false);
        });
    }
});