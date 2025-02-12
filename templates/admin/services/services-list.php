<?php defined('ABSPATH') or die('No script kiddies please!');// No direct access

if (!empty($services)) {
    ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th class="cell-xm">
                <div class="checkbox">
                    <input type="checkbox" id="bm-checkbox-all">
                    <label for="bm-checkbox-all"><span class="checkbox-icon"></span></label>
                </div>
            </th>
            <th><?php esc_html_e('Name', 'bookme') ?></th>
            <th><?php esc_html_e('Duration', 'bookme') ?></th>
            <th><?php esc_html_e('Price', 'bookme') ?></th>
            <th class="cell-xm"></th>
        </tr>
        </thead>
        <tbody id="services-tbody">
        <?php foreach ($services as $service) { ?>
            <tr data-service-id="<?php echo $service['id'] ?>">
                <td>
                    <div class="checkbox">
                        <input type="checkbox" id="check_<?php echo $service['id'] ?>" value="<?php echo $service['id'] ?>" class="bm-check">
                        <label for="check_<?php echo $service['id'] ?>"><span class="checkbox-icon"></span></label>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div>
                            <i class="icon-feather-menu bookme-reorder-icon"
                               title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
                        </div>
                        <div class="service-color-box"
                             style="background-color: <?php echo esc_attr($service['color']) ?>">
                        </div>
                        <div>
                            <h6 class="m-0">
                                <?php echo esc_html($service['title']) ?>
                            </h6>
                        </div>
                    </div>
                </td>
                <td>
                    <?php echo \Bookme\Inc\Mains\Functions\DateTime::seconds_to_interval($service['duration']); ?>
                </td>
                <td>
                    <?php echo \Bookme\Inc\Mains\Functions\Price::format($service['price']) ?>
                </td>
                <td class="actions">
                    <div class="table-content">
                        <button data-tippy-placement="top"
                                title="<?php esc_html_e('Edit', 'bookme'); ?>"
                                class="btn-icon"
                                data-url="<?php echo add_query_arg(array('service_id' => $service['id']), $service_panel_url); ?>"
                                data-toggle="slidePanel">
                            <i class="icon-feather-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php }