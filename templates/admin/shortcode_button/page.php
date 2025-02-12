<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<style type="text/css">
    .bookme-shortcode-form {margin-top: 15px;}
    .bookme-shortcode-form table {width: 100%;}
    .bookme-shortcode-form table td {padding: 5px;vertical-align: 0;}
    .bookme-shortcode-form table th.bookme-title-col {width: 80px;}
    .bookme-shortcode-form table td select {width: 100%;margin-bottom: 5px;}
    .bookme-shortcode-form table td, .bookme-shortcode-form table th  {text-align: left}
    #add-bookme-form {margin-bottom: 10px;}
    .bookme-media-icon {display: inline-block;width: 16px;height: 16px;vertical-align: text-top;margin: 0 2px;background: url("<?php echo BOOKME_URL.'assets/admin/images/menu-logo.png' ?>") 0 0 no-repeat;}
    .components-button .bookme-media-icon {margin: 4px 6px 4px 0;}
    #TB_overlay {z-index: 100001 !important;}
    #TB_window {z-index: 100002 !important;}
</style>
<div id="bookme-tinymce-popup" style="display: none">
    <form class="bookme-shortcode-form">
        <table>
            <tr>
                <td>
                    <label for="bookme-select-category"><?php echo \Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_category') ?></label>
                </td>
                <td>
                    <select id="bookme-select-category">
                        <option value=""><?php esc_html_e('Select default value', 'bookme') ?></option>
                    </select>
                    <div><label><input type="checkbox" id="bookme-hide-categories"/><?php esc_html_e('Hide this field', 'bookme') ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bookme-select-service"><?php echo \Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_service') ?></label>
                </td>
                <td>
                    <select id="bookme-select-service">
                        <option value=""><?php esc_html_e('Select default value', 'bookme') ?></option>
                    </select>
                    <div><label><input type="checkbox" id="bookme-hide-services"/><?php esc_html_e('Hide this field', 'bookme') ?></label></div>
                    <em><?php esc_html_e("A default value is required for this field, If you want to hide it.") ?></em>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bookme-select-employee"><?php echo \Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_employee') ?></label>
                </td>
                <td>
                    <select class="bookme-select-mobile" id="bookme-select-employee">
                        <option value=""><?php esc_html_e('Any', 'bookme') ?></option>
                    </select>
                    <div><label><input type="checkbox" id="bookme-hide-employee"/><?php esc_html_e('Hide this field', 'bookme') ?></label></div>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bookme-hide-number-of-persons"><?php echo \Bookme\Inc\Mains\Functions\System::get_translated_option('bookme_lang_title_number_of_persons') ?></label>
                </td>
                <td>
                    <label><input type="checkbox" id="bookme-hide-number-of-persons" checked/><?php esc_html_e('Hide this field', 'bookme') ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input class="button button-primary" id="bookme-insert-shortcode" type="submit" value="<?php esc_html_e('Insert', 'bookme') ?>"/>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="bookme-tinymce-bookings-popup" style="display: none">
    <form class="bookme-shortcode-form" id="bookme-bookings-form">
        <table>
            <tr>
                <th class="bookme-title-col"><?php esc_html_e( 'Columns', 'bookme' ) ?></th>
                <td>
                    <label><input type="checkbox" data-column="category" /><?php esc_html_e( 'Category', 'bookme' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="service" checked /><?php esc_html_e( 'Service', 'bookme' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="staff" checked /><?php esc_html_e( 'Staff', 'bookme' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="date" checked /><?php esc_html_e( 'Date', 'bookme' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="time" checked /><?php esc_html_e( 'Time', 'bookme' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="price" checked /><?php esc_html_e( 'Price', 'bookme' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="status" checked /><?php esc_html_e( 'Status', 'bookme' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="cancel" checked /><?php esc_html_e( 'Cancel', 'bookme' ) ?></label>
                </td>
            </tr>
            <tr>
                <th colspan="2"><?php esc_html_e( 'Custom Fields', 'bookme' ) ?></th>
            </tr>
            <?php
            $custom_fields = array_filter( json_decode( get_option( 'bookme_custom_fields' ) ), function ( $field ) {
                return ! in_array( $field->type, array( 'text-content' ) );
            } );
            foreach ( $custom_fields as $field ) : ?>
                <tr>
                    <td><?php echo $field->label ?></td>
                    <td>
                        <label><input type="checkbox" data-custom_field="<?php echo $field->id ?>" /><?php esc_html_e( 'Yes', 'bookme' ) ?></label>
                    </td>
                </tr>
            <?php endforeach ?>
            <tr>
                <td></td>
                <td>
                    <input class="button button-primary" id="bookme-insert-bookings-shortcode" type="submit" value="<?php esc_attr_e( 'Insert', 'bookme' ) ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>

<script type="text/javascript">
    jQuery(function ($) {
        var $select_category = $('#bookme-select-category'),
            $select_service = $('#bookme-select-service'),
            $select_employee = $('#bookme-select-employee'),
            $hide_categories = $('#bookme-hide-categories'),
            $hide_services = $('#bookme-hide-services'),
            $hide_staff = $('#bookme-hide-employee'),
            $hide_number_of_persons = $('#bookme-hide-number-of-persons'),
            $insert_form_shortcode = $('#bookme-insert-shortcode'),
            $insert_bookings_shortcode = $('#bookme-insert-bookings-shortcode'),
            categories = <?php echo json_encode($data['categories']) ?>,
            services = <?php echo json_encode($data['services']) ?>,
            staff = <?php echo json_encode($data['staff']) ?>;


        // Category select change
        $select_category.on('change', function () {
            var category_id = this.value,
                service_id = $select_service.val(),
                staff_id = $select_employee.val();

            // Validate selected values.
            if (category_id != '') {
                if (service_id != '') {
                    if (services[service_id].category_id != category_id) {
                        service_id = '';
                    }
                }
                if (staff_id != '') {
                    var valid = false;
                    $.each(staff[staff_id].services, function (id) {
                        if (services[id].category_id == category_id) {
                            valid = true;
                            return false;
                        }
                    });
                    if (!valid) {
                        staff_id = '';
                    }
                }
            }
            set_selects(category_id, service_id, staff_id);
        });

        $select_service.on('change', function () {
            var category_id = '',
                service_id = this.value,
                staff_id = $select_employee.val();

            // Validate selected values.
            if (service_id != '') {
                if (staff_id != '' && !staff[staff_id].services.hasOwnProperty(service_id)) {
                    staff_id = '';
                }
            }
            set_selects(category_id, service_id, staff_id);
            if (service_id) {
                $select_category.val(services[service_id].category_id);
            }
        });

        $select_employee.on('change', function () {
            var category_id = $select_category.val(),
                service_id = $select_service.val(),
                staff_id = this.value;

            set_selects(category_id, service_id, staff_id);
        });

        // Set up default values
        set_select($select_category, categories);
        set_select($select_service, services);
        set_select($select_employee, staff);

        $insert_form_shortcode.on('click', function (e) {
            e.preventDefault();
            window.send_to_editor(getFormShortcode());
            clearFields();
            window.parent.tb_remove();
            return false;
        });

        $insert_bookings_shortcode.on('click', function (e) {
            e.preventDefault();
            window.send_to_editor(getBookingsShortcode());
            window.parent.tb_remove();
            return false;
        });

        <?php if (\Bookme\Inc\Mains\Functions\System::is_gutenberg_page()){ ?>
        var properties = null,
            el = wp.element.createElement;
        var withInspectorControls = wp.compose.createHigherOrderComponent(function (BlockEdit) {
            return function (props) {
                properties = props;
                if (props.name != 'core/shortcode')
                    return el(
                        wp.element.Fragment,
                        null,
                        el(
                            BlockEdit,
                            props
                        )
                    );

                return el(
                    wp.element.Fragment,
                    null,
                    el(
                        BlockEdit,
                        props
                    ),
                    el(
                        wp.editor.InspectorControls,
                        null,
                        el(
                            wp.components.PanelBody,
                            {
                                title: '<?php _e('Bookme Shortcode', 'bookme') ?>',
                                className: 'block-social-links',
                                initialOpen: true
                            },
                            el(
                                wp.components.Button,
                                {
                                    id: 'add-bookme-form',
                                    className: 'is-button is-default bookme-media-button',
                                    onClick: function () {
                                        openFormModal();
                                    }
                                },
                                el(
                                    'span',
                                    {
                                        className: 'bookme-media-icon'
                                    }
                                ),
                                '<?php _e('Add Bookme booking form', 'bookme') ?>'
                            ),
                            el(
                                wp.components.Button,
                                {
                                    id: 'add-bookme-bookings',
                                    className: 'is-button is-default bookme-media-button',
                                    onClick: function () {
                                        openBookingsModal();
                                    }
                                },
                                el(
                                    'span',
                                    {
                                        className: 'bookme-media-icon'
                                    }
                                ),
                                '<?php _e('Bookme Customer Bookings', 'bookme') ?>'
                            )
                        )
                    )
                );
            };
        }, 'withInspectorControls');
        wp.hooks.addFilter('editor.BlockEdit', 'Bookme', withInspectorControls);

        $insert_form_shortcode.off('click').on('click', function (e) {
            e.preventDefault();
            properties.setAttributes({text: getFormShortcode()});
            clearFields();
            window.parent.tb_remove();
            return false;
        });
        $insert_bookings_shortcode.off('click').on('click', function (e) {
            e.preventDefault();
            properties.setAttributes({text: getBookingsShortcode()});
            window.parent.tb_remove();
            return false;
        });
        <?php } ?>

        // helper functions
        function openFormModal() {
            window.parent.tb_show(<?php echo json_encode(__('Bookme Form Shortcode', 'bookme')) ?>, '#TB_inline?width=640&inlineId=bookme-tinymce-popup&height=650');
            window.setTimeout(function () {
                $('#TB_window').css({
                    'overflow-x': 'auto',
                    'overflow-y': 'hidden'
                });
            }, 100);
        }

        function openBookingsModal() {
            window.parent.tb_show(<?php echo json_encode(__('Bookme Customer Bookings Shortcode', 'bookme')) ?>, '#TB_inline?width=640&inlineId=bookme-tinymce-bookings-popup&height=650');
            window.setTimeout(function () {
                $('#TB_window').css({
                    'overflow-x': 'auto',
                    'overflow-y': 'hidden'
                });
            }, 100);
        }

        function getFormShortcode() {
            var insert = '[bookme';
            var hide = [];
            if ($select_category.val()) {
                insert += ' category_id="' + $select_category.val() + '"';
            }
            if ($hide_categories.is(':checked')) {
                hide.push('categories');
            }
            if ($select_service.val()) {
                insert += ' service_id="' + $select_service.val() + '"';
            }
            if ($hide_services.is(':checked')) {
                hide.push('services');
            }
            if ($select_employee.val()) {
                insert += ' staff_member_id="' + $select_employee.val() + '"';
            }
            if ($hide_staff.is(':checked')) {
                hide.push('staff_members');
            }
            if ($hide_number_of_persons.is(':not(:checked)')) {
                insert += ' show_number_of_persons="1"';
            }
            if (hide.length > 0) {
                insert += ' hide="' + hide.join() + '"';
            }
            return insert += ']';
        }

        function getBookingsShortcode() {
            var insert = '[bookme-customer-bookings';

            // columns
            var columns = $('[data-column]:checked');
            if (columns.length) {
                column = [];
                $.each(columns, function() {
                    column.push($(this).data('column'));
                });
                insert += ' columns="' + column.join(',') + '"';
            }
            // custom_fields
            var custom_fields = $('[data-custom_field]:checked');
            if (custom_fields.length) {
                column = [];
                $.each(custom_fields, function() {
                    column.push($(this).data('custom_field'));
                });
                insert += ' custom_fields="' + column.join(',') + '"';
            }

            return insert += ']';
        }

        function clearFields() {
            $select_category.val('');
            $select_service.val('');
            $select_employee.val('');
            $hide_categories.prop('checked', false);
            $hide_services.prop('checked', false);
            $hide_staff.prop('checked', false);
            $hide_number_of_persons.prop('checked', true);
        }

        function set_select($select, data, value) {
            // reset select
            $('option:not([value=""])', $select).remove();
            // and fill the new data
            var docFragment = document.createDocumentFragment();

            function valuesToArray(obj) {
                return Object.keys(obj).map(function (key) {
                    return obj[key];
                });
            }

            function compare(a, b) {
                if (parseInt(a.pos) < parseInt(b.pos))
                    return -1;
                if (parseInt(a.pos) > parseInt(b.pos))
                    return 1;
                return 0;
            }

            // sort select by position
            data = valuesToArray(data).sort(compare);

            $.each(data, function (key, object) {
                var option = document.createElement('option');
                option.value = object.id;
                option.text = object.name;
                docFragment.appendChild(option);
            });
            $select.append(docFragment);
            // set default value of select
            $select.val(value);
        }

        function set_selects(category_id, service_id, staff_id) {
            var _staff = {}, _services = {}, _categories = {}, _nop = {};
            $.each(staff, function (id, staff_member) {
                if (service_id == '') {
                    if (category_id == '') {
                        _staff[id] = staff_member;
                    } else {
                        $.each(staff_member.services, function (s_id) {
                            if (services[s_id].category_id == category_id) {
                                _staff[id] = staff_member;
                                return false;
                            }
                        });
                    }
                } else if (staff_member.services.hasOwnProperty(service_id)) {
                    if (staff_member.services[service_id].price != null) {
                        _staff[id] = {
                            id: id,
                            name: staff_member.name + ' (' + staff_member.services[service_id].price + ')',
                            pos: staff_member.pos
                        };
                    } else {
                        _staff[id] = staff_member;
                    }
                }
            });
            _categories = categories;
            $.each(services, function (id, service) {
                if (category_id == '' || service.category_id == category_id) {
                    if (staff_id == '' || staff[staff_id].services.hasOwnProperty(id)) {
                        _services[id] = service;
                    }
                }
            });
            set_select($select_category, _categories, category_id);
            set_select($select_service, _services, service_id);
            set_select($select_employee, _staff, staff_id);
        }
    });
</script>