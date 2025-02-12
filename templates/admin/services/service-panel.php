<?php defined('ABSPATH') or die('No script kiddies please!');// No direct access

use \Bookme\Inc\Mains\Functions\System;
use \Bookme\Inc\Mains\Functions\DateTime;
use \Bookme\Inc\Mains\Tables\Service;

$time_interval = get_option('bookme_time_slot_step');
$service_id = isset($service['id']) ? $service['id'] : null;
$assigned_staff_ids = isset($service['staff_ids']) ? explode(',', $service['staff_ids']) : array();
$all_staff_selected = count($assigned_staff_ids) == count($employees);
?>

<div class="bookme-page-wrapper">
    <header class="slidePanel-header">
        <div class="slidePanel-overlay-panel">
            <div class="slidePanel-heading">
                <h2><?php echo !empty($service['title'])? $service['title'] : esc_html__('Add Service', 'bookme') ?></h2>
            </div>
            <div class="slidePanel-actions">
                <button class="btn-icon btn-primary ajax-save-service" title="<?php esc_attr_e('Save', 'bookme') ?>">
                    <i class="icon-feather-check"></i>
                </button>
                <?php if($service_id) { ?>
                    <button class="btn-icon btn-danger ajax-delete-service" title="<?php esc_attr_e('Delete', 'bookme') ?>">
                        <i class="icon-feather-trash-2"></i>
                    </button>
                    <?php
                }
                ?>
                <button class="btn-icon slidePanel-close" title="<?php esc_attr_e('Close', 'bookme') ?>">
                    <i class="icon-feather-x"></i>
                </button>
            </div>
        </div>
    </header>
    <div class="slidePanel-inner">
        <form method="post" class="theme-form">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="title"><?php esc_html_e('Title', 'bookme') ?></label>
                        <div class="d-flex align-items-center">
                            <div class="bm-color-picker-wrapper">
                                <button class="bm-color-picker"></button>
                                <input type="hidden" class="color-input" name="color"
                                       value="<?php echo isset($service['color']) ? esc_attr($service['color']): null; ?>">
                            </div>
                            <div class="flex-grow-1 m-l-20 form-required">
                                <input name="title" value="<?php echo esc_attr($service['title']) ?>"
                                       id="title" class="form-control" type="text">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="duration">
                            <?php esc_html_e('Duration', 'bookme') ?>
                        </label>
                        <select id="duration" class="form-control"
                                name="duration">
                            <?php for ($j = $time_interval; $j <= 720; $j += $time_interval) { ?><?php if ($service['duration'] / 60 > $j - $time_interval && $service['duration'] / 60 < $j) { ?>
                                <option value="<?php echo esc_attr($service['duration']) ?>"
                                        selected><?php echo DateTime::seconds_to_interval($service['duration']) ?></option><?php } ?>
                                <option
                                value="<?php echo $j * 60 ?>" <?php selected($service['duration'], $j * 60) ?>><?php echo DateTime::seconds_to_interval($j * 60) ?></option><?php } ?>
                            <?php for ($j = 86400; $j <= 604800; $j += 86400) { ?>
                                <option
                                value="<?php echo $j ?>" <?php selected($service['duration'], $j) ?>><?php echo DateTime::seconds_to_interval($j) ?></option><?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="price"><?php esc_html_e('Price', 'bookme') ?></label>
                        <input id="price" class="form-control bookme-question"
                               type="number" min="0" step="1" name="price"
                               value="<?php echo isset($service['price']) ? esc_attr($service['price']): null ?>">
                    </div>
                </div>
            </div>
            <div class="row" style="display: none">
                <div class="col-6">
                    <div class="form-group">
                        <label for="start_time_info"><?php esc_html_e('Start time of the appointment', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Set the start time of the appointment with service duration longer than 1 day. This time will be displayed in notifications to customers.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <input id="start_time_info" class="form-control"
                               type="text" name="start_time_info"
                               value="<?php echo isset($service['start_time_info']) ? esc_attr($service['start_time_info']): null ?>">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="end_time_info"><?php esc_html_e('End time of the appointment', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Set the end time of the appointment with service duration longer than 1 day. This time will be displayed in notifications to customers.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <input id="end_time_info" class="form-control" type="text" name="end_time_info"
                               value="<?php echo isset($service['end_time_info']) ? esc_attr($service['end_time_info']): null ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="padding_left">
                            <?php esc_html_e('Padding time before', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Set a time before the appointment for preparation or any other thing. When another booking with same service and employee can not be made.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select id="padding_left" class="form-control"
                                name="padding_left">
                            <option value="0"><?php esc_html_e('OFF', 'bookme') ?></option>
                            <?php for ($j = $time_interval; $j <= 1440; $j += $time_interval) { ?><?php if ($service['padding_left'] > 0 && $service['padding_left'] / 60 > $j - $time_interval && $service['padding_left'] / 60 < $j) { ?>
                                <option value="<?php echo esc_attr($service['padding_left']) ?>"
                                        selected><?php echo DateTime::seconds_to_interval($service['padding_left']) ?></option><?php } ?>
                                <option
                                value="<?php echo $j * 60 ?>" <?php selected($service['padding_left'], $j * 60) ?>><?php echo DateTime::seconds_to_interval($j * 60) ?></option><?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="padding_right">
                            <?php esc_html_e('Padding time after', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Set a time after the appointment for rest, clean up, etc. When another booking with same service and staff can not be made.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select id="padding_right" class="form-control"
                                name="padding_right">
                            <option value="0"><?php esc_attr_e('OFF', 'bookme') ?></option>
                            <?php for ($j = $time_interval; $j <= 1440; $j += $time_interval) { ?><?php if ($service['padding_right'] > 0 && $service['padding_right'] / 60 > $j - $time_interval && $service['padding_right'] / 60 < $j) { ?>
                                <option value="<?php echo esc_attr($service['padding_right']) ?>"
                                        selected><?php echo DateTime::seconds_to_interval($service['padding_right']) ?></option><?php } ?>
                                <option
                                value="<?php echo $j * 60 ?>" <?php selected($service['padding_right'], $j * 60) ?>><?php echo DateTime::seconds_to_interval($j * 60) ?></option><?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="capacity"><?php esc_html_e('Capacity (min and max)', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Here you can set the minimum and maximum number of persons for one booking.', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <div class="row">
                            <div class="col-6">
                                <input id="capacity_min"
                                       class="form-control bookme-question bookme-capacity-min"
                                       type="number" min="1"
                                       step="1" name="capacity_min"
                                       value="<?php echo !empty($service['capacity_min'])?esc_attr($service['capacity_min']):1 ?>">
                            </div>
                            <div class="col-6">
                                <input id="capacity_max"
                                       class="form-control bookme-question bookme-capacity-max"
                                       type="number" min="1"
                                       step="1" name="capacity_max"
                                       value="<?php echo !empty($service['capacity_max'])?esc_attr($service['capacity_max']):1 ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="visibility"><?php esc_html_e('Visibility', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('To hide the service from your customers set the visibility to "Private".', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select name="visibility" class="form-control"
                                id="visibility">
                            <option value="public" <?php selected($service['visibility'], 'public') ?>><?php esc_attr_e('Public', 'bookme') ?></option>
                            <option value="private" <?php selected($service['visibility'], 'private') ?>><?php esc_attr_e('Private', 'bookme') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group form-required">
                        <label for="category"><?php esc_html_e('Category', 'bookme') ?></label>
                        <select id="category" class="form-control" name="category_id">
                            <option value=""><?php esc_attr_e('Select Category', 'bookme') ?></option>
                            <?php foreach ($categories as $category) { ?>
                                <option value="<?php echo $category['id'] ?>" <?php selected($category['id'], $service['category_id']) ?>><?php echo esc_html($category['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="bookme-js-staff-selector"><?php esc_html_e('Staff Members', 'bookme') ?></label><br>
                        <select name="staff_ids[]" id="bookme-employees" class="form-control"
                                multiple
                                data-placeholder="<?php esc_attr_e('Select Staff Members', 'bookme') ?>"
                                data-nothing="<?php esc_attr_e('No staff selected', 'bookme') ?>"
                                data-selected="<?php esc_attr_e('selected', 'bookme') ?>"
                                data-selectall="<?php esc_attr_e('Select All', 'bookme') ?>"
                                data-unselectall="<?php esc_attr_e('Unselect All', 'bookme') ?>"
                                data-allselected="<?php esc_attr_e('All Staffs Selected', 'bookme') ?>">
                            <?php foreach ($employees as $i => $staff) { ?>
                                <option value="<?php echo $staff['id'] ?>" <?php selected(in_array($staff['id'], $assigned_staff_ids)) ?>><?php echo esc_html($staff['full_name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="staff_preference">
                            <?php esc_html_e('Staff preference for ANY', 'bookme') ?>
                            <i class="dashicons dashicons-editor-help"
                               title="<?php esc_attr_e('Select staff members auto assignment when "ANY" option is selected', 'bookme') ?>"
                               data-tippy-placement="top"></i>
                        </label>
                        <select id="staff_preference" class="form-control"
                                name="staff_preference"
                                data-default="[<?php echo $service['pref_staff_ids'] ?>]">
                            <?php
                            $service['staff_preference'] = isset($service['staff_preference']) ? $service['staff_preference'] : Service::PREFERRED_MOST_EXPENSIVE;
                            foreach ($employee_preference as $rule => $name) { ?>
                                <option
                                value="<?php echo $rule ?>" <?php selected($rule == $service['staff_preference']) ?>><?php echo $name ?></option><?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 bookme-employees-wrapper">
                    <div class="form-group">
                        <label for="staff_preferred"><?php esc_html_e('Staff Members', 'bookme') ?></label><br/>
                        <ul class="bookme-employees-list"
                             data-service_id="<?php echo $service_id ?>"></ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <?php $service['online_meeting'] = isset($service['online_meeting']) ? $service['online_meeting'] : 'off'; ?>
                    <div class="form-group">
                        <label for="online_meeting"><?php esc_html_e('Zoom', 'bookme') ?> <i class="dashicons dashicons-editor-help" title="<?php esc_attr_e('Enable online meeting for this service.', 'bookme') ?>" data-tippy-placement="top"></i></label>
                        <div class="form-toggle-option">
                            <div>
                                <label for="online_meeting"><?php esc_html_e('Enable', 'bookme') ?></label>
                            </div>
                            <div>
                                <input type="hidden" name="online_meeting" value="off">
                                <label class="switch switch-sm">
                                    <input name="online_meeting" type="checkbox" id="online_meeting"  value="zoom" <?php checked($service['online_meeting'],'zoom') ?>>
                                    <span class="switch-state"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <label for="bookings_limit">
                    <?php esc_html_e('Limit bookings per customer', 'bookme') ?>
                    <i class="dashicons dashicons-editor-help"
                       title="<?php esc_attr_e('Limit the number of bookings per customer.', 'bookme') ?>" data-tippy-placement="top"></i>
                </label>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <input id="bookings_limit"
                                   class="form-control"
                                   type="number" min="0" step="1" name="bookings_limit"
                                   value="<?php echo isset($service['bookings_limit']) ? esc_attr($service['bookings_limit']): null ?>">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <select id="limit_period" class="form-control"
                                    name="limit_period">
                                <option value="off"><?php esc_attr_e('OFF', 'bookme') ?></option>
                                <option value="day"<?php selected('day', $service['limit_period']) ?>><?php esc_attr_e('per day', 'bookme') ?></option>
                                <option value="week"<?php selected('week', $service['limit_period']) ?>><?php esc_attr_e('per week', 'bookme') ?></option>
                                <option value="month"<?php selected('month', $service['limit_period']) ?>><?php esc_attr_e('per month', 'bookme') ?></option>
                                <option value="year"<?php selected('year', $service['limit_period']) ?>><?php esc_attr_e('per year', 'bookme') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="info"><?php esc_html_e('Info', 'bookme') ?></label>
                <textarea class="form-control" id="info" name="info" rows="2"
                          type="text"><?php echo isset($service['info']) ? esc_attr($service['info']): null ?></textarea>
            </div>
            <input type="hidden" name="action" value="bookme_update_service">
            <input type="hidden" name="id" value="<?php echo esc_attr($service_id) ?>">
            <input type="hidden" name="update_staff" value="0">
            <?php System::csrf() ?>
        </form>
    </div>
</div>