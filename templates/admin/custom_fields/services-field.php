<select name="service_ids[]" class="form-control bm-services"
        multiple data-placeholder="<?php esc_attr_e('Select Services', 'bookme') ?>" data-nothing="<?php esc_attr_e('No Service selected', 'bookme') ?>" data-selected="<?php esc_attr_e('selected', 'bookme') ?>" data-selectall="<?php esc_attr_e('Select All', 'bookme') ?>" data-unselectall="<?php esc_attr_e('Unselect All', 'bookme') ?>" data-allselected="<?php esc_attr_e('All Services Selected', 'bookme') ?>">
    <?php foreach ($all_services as $category => $services) { ?>
        <optgroup label="<?php echo esc_attr($category) ?>">
            <?php foreach ($services as $service) { ?>
                <option value="<?php echo $service['id'] ?>"><?php echo esc_attr($service['title']) ?></option>
            <?php } ?>
        </optgroup>
    <?php } ?>
</select>