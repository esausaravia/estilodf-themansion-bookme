<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access

 foreach ( $custom_fields as $field_id ) { ?>
    <td>
        <?php if ( array_key_exists( $field_id, $app['custom_fields'] ) ) { ?>
            <?php echo $app['custom_fields'][ $field_id ] ?>
        <?php } ?>
    </td>
<?php } ?>