<?php defined('ABSPATH') or die('No script kiddies please!');// No direct access

$img = wp_get_attachment_image_src($member['attachment_id'], 'thumbnail');
$img_url = $img ? $img[0] : BOOKME_URL . '/assets/admin/images/user-default.png';
?>
<tr id="bookme-employee-<?php echo $member['id'] ?>" data-id="<?php echo $member['id'] ?>">
    <td>
        <div class="checkbox">
            <input type="checkbox" id="check_<?php echo $member['id'] ?>" value="<?php echo $member['id'] ?>"
                   class="bm-check">
            <label for="check_<?php echo $member['id'] ?>"><span class="checkbox-icon"></span></label>
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <div class="m-r-5">
                <i class="icon-feather-menu bookme-reorder-icon"
                   title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
            </div>
            <div class="image-box m-r-10">
                <img class="img-round"
                     src="<?php echo esc_url($img_url); ?>"
                     alt="<?php echo esc_html($member['full_name']); ?>">
            </div>
            <div>
                <h6 class="m-0">
                    <?php echo esc_html($member['full_name']); ?>
                    <?php if ($member['visibility'] == 'private') { ?>
                        <i class="icon-feather-eye-off"
                           data-tippy-placement="top"
                           title="<?php esc_html_e('Private', 'bookme'); ?>"></i>
                    <?php } ?>
                </h6>
            </div>
        </div>
    </td>
    <td class="staff-services">
        <?php if (!empty($member['services'])) {
            $services = explode(',', $member['services']);
            $rest = count($services) - 2;
            $services = array_slice($services, 0, 2);
            foreach ($services as $service) {
                echo "<div class='badge badge-primary'>$service</div>";
            }
            if ($rest > 0)
                echo "<div class='badge badge-primary'>+" . $rest . "</div>";
        } else {
            esc_html_e('No services assigned', 'bookme');
        } ?>
    </td>
    <td>
        <?php echo esc_html($member['email']); ?><br>
        <?php echo esc_html($member['phone']); ?>
    </td>
    <td class="actions">
        <div class="table-content">
            <button data-tippy-placement="top"
                    title="<?php esc_html_e('Edit', 'bookme'); ?>"
                    class="btn-icon"
                    data-url="<?php echo add_query_arg(array('id' => $member['id']), $edit_employee_panel_url); ?>"
                    data-toggle="slidePanel">
                <i class="icon-feather-edit"></i>
            </button>
            <button data-tippy-placement="top"
                    title="<?php esc_html_e('Days Off', 'bookme'); ?>"
                    class="btn-icon"
                    data-url="<?php echo add_query_arg(array('id' => $member['id']), $edit_holidays_panel_url); ?>"
                    data-toggle="slidePanel" data-event="holidays_panel">
                <i class="icon-feather-calendar"></i>
            </button>
        </div>
    </td>
</tr>