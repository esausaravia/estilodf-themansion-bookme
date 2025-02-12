<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
include 'css.php';

if (trim($custom_css)) { ?>
    <style type="text/css">
        <?php echo $custom_css; ?>
    </style>
<?php } ?>
<div id="bookme-booking-form-<?php echo $form_id ?>" class="bookme-booking-form" data-form_id="<?php echo $form_id ?>"><div class="bookme-box-loader"><div class="bookme-loader"></div></div></div>
<script>
    (function ($) {
        window.bookme(
            <?php echo json_encode($form_id) ?>,
            <?php echo json_encode($attrs) ?>,
            <?php echo json_encode($skip_steps) ?>,
            <?php echo json_encode($status) ?>
        );
    })(jQuery);
</script>