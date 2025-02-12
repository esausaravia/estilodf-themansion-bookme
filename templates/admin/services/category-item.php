<?php defined('ABSPATH') or die('No script kiddies please!');// No direct access ?>
<li class="category-item" data-id="<?php echo esc_attr($category['id']); ?>">
    <div>
        <i class="icon-feather-menu bookme-reorder-icon"
           title="<?php esc_attr_e('Reorder', 'bookme') ?>"></i>
    </div>
    <div class="category-item-form">
        <span class="category-item-name"><?php echo esc_html($category['name']); ?></span>
        <input type="text" class="category-item-input" value="<?php echo esc_attr($category['name']); ?>">
    </div>
    <a href="#" class="icon-feather-edit mx-1 category-item-edit" title="<?php esc_attr_e('Edit', 'bookme') ?>" data-tippy-placement="top"></a>
    <a href="#" class="icon-feather-trash-2 category-item-delete" title="<?php esc_attr_e('Delete', 'bookme') ?>" data-tippy-placement="top"></a>
</li>