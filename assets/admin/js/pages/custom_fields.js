jQuery(function ($) {

    var $fields = $("#bm-custom-fields"),
        $cf_per_service = $('#bookme_custom_fields_per_service'),
        $cf_merge = $('#bookme_custom_fields_merge');

    $fields.sortable({
        axis: 'y',
        handle: '.bookme-reorder-icon'
    });


    $cf_per_service.on('change', function () {
        $('.bm-service-field').toggle($(this).is(':checked'));
    }).trigger('change');

    /**
     * Build fields.
     */
    build_fields();


    $('.bm-add-fields').on('click', 'button', function () {
        add_field($(this).data('type'));
    });


    $fields.on('click', '.bm-custom-field-item', function () {
        add_item($(this).prev('.bm-items'), $(this).data('type'));
    });


    $fields.on('click', '.bm-custom-field-delete', function (e) {
        e.preventDefault();
        if(confirm(Bookme.are_you_sure)) {
            $(this).closest('.bm-cf').fadeOut('fast', function () {
                $(this).remove();
            });
        }
    });

    /**
     * Submit custom fields form.
     */
    $('#ajax-save-custom-fields').on('click', function (e) {
        e.preventDefault();
        var $button = $(this),
            data = [];

        $fields.children('div').each(function () {
            var $this = $(this),
                field = {};
            switch ($this.data('type')) {
                case 'checkboxes':
                case 'radio-buttons':
                case 'drop-down':
                    field.items = [];
                    $this.find('.bm-items > div').each(function () {
                        field.items.push($(this).find('input').val());
                    });
                case 'textarea':
                case 'text-field':
                case 'text-content':
                    field.type = $this.data('type');
                    field.label = $this.find('.bm-label').val();
                    field.required = $this.find('.bm-required').prop('checked');
                    field.id = $this.data('bm-id');
                    field.services = $this.find('.bm-service-field .bm-services').val()
            }
            data.push(field);
        });

        $button.addClass('bookme-loader').prop('disabled',true);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            xhrFields: {withCredentials: true},
            data: {
                action: 'bookme_save_custom_fields',
                csrf_token: Bookme.csrf_token,
                fields: JSON.stringify(data),
                bookme_custom_fields_per_service: $cf_per_service.is(':checked')?1:0,
                bookme_custom_fields_merge: $cf_merge.is(':checked')?1:0
            },
            complete: function () {
                $button.removeClass('bookme-loader').prop('disabled',false);
                bm_alert(Bookme.saved);
            }
        });
    });

    /**
     * Add new field.
     *
     * @param type
     * @param id
     * @param label
     * @param required
     * @param services
     * @returns {*|jQuery}
     */
    function add_field(type, id, label, required, services) {
        var $new_field = $('#bm-custom-fields-templates > div[data-type=' + type + ']').clone();
        // Set id, label and required.
        if (typeof id == 'undefined') {
            id = Math.floor((Math.random() * 100000) + 1);
        }
        if (typeof label == 'undefined') {
            label = '';
        }
        if (typeof required == 'undefined') {
            required = false;
        }
        $new_field
            .hide()
            .data('bm-id', id)
            .find('.bm-required').prop({
            id: 'required-' + id,
            checked: required
        })
            .next('label').attr('for', 'required-' + id)
            .end().end()
            .find('.bm-label').val(label)
            .end()
            .find('.bm-service-field .bm-services').val(services);

        var $service_selector = $new_field.find('.bm-service-field .bm-services');
        $service_selector.multiselect({
            texts: {
                placeholder: $service_selector.data('placeholder'), // text to use in dummy input
                selectedOptions: ' ' + $service_selector.data('selected'),      // selected suffix text
                selectAll: $service_selector.data('selectall'),     // select all text
                unselectAll: $service_selector.data('unselectall'),   // unselect all text
                noneSelected: $service_selector.data('nothing'),   // None selected text
                allSelected: $service_selector.data('allselected')
            },
            showCheckbox: false,  // display the checkbox to the user
            selectAll: true, // add select all option
            minHeight: 20,
            maxPlaceholderOpts : 1
        });

        // Add new field to the list.
        $fields.append($new_field);
        $new_field.fadeIn('fast');
        // Make it sortable.
        $new_field.find('.bm-items').sortable({
            axis: 'y',
            handle: '.bookme-reorder-icon'
        });
        // Set focus to label field.
        $new_field.find('.bm-label').focus();

        return $new_field;
    }

    /**
     * Add new checkbox/radio button/drop-down option.
     *
     * @param $div
     * @param type
     * @param value
     * @return {*|jQuery}
     */
    function add_item($div, type, value) {
        var $new_item = $('#bm-custom-fields-templates > div[data-type=' + type + ']').clone();
        if (typeof value != 'undefined') {
            $new_item.find('input').val(value);
        }
        $new_item.hide().appendTo($div).fadeIn('fast').find('input').focus();

        return $new_item;
    }

    /**
     * Build fields from Bookme.custom_fields.
     */
    function build_fields() {
        if (Bookme.custom_fields) {
            var custom_fields = jQuery.parseJSON(Bookme.custom_fields);
            $.each(custom_fields, function (i, field) {
                var $new_field = add_field(field.type, field.id, field.label, field.required, field.services);
                // add children
                if (field.items) {
                    $.each(field.items, function (i, value) {
                        add_item($new_field.find('.bm-items'), field.type + '-item', value);
                    });
                }
            });
        }
        $cf_per_service.trigger('change');
        $(':focus').blur();
    }
});