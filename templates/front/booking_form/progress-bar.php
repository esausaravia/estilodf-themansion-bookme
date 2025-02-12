<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
use Bookme\Inc\Mains\Functions\System; ?>
<ul class="bookme-steps">
    <li <?php if ($step == 1) { ?>class="bookme-steps-is-active"<?php } ?>>
        <span><?php echo System::get_translated_option('bookme_lang_step_service') ?></span>
    </li>
    <li <?php if ($step == 2) { ?>class="bookme-steps-is-active"<?php } ?>>
        <span><?php echo System::get_translated_option('bookme_lang_step_time') ?></span>
    </li>
    <?php if ($show_cart) { ?>
        <li <?php if ($step == 3) { ?>class="bookme-steps-is-active"<?php } ?>>
            <span><?php echo System::get_translated_option('bookme_lang_step_cart') ?></span>
        </li>
    <?php } ?>
    <li <?php if ($step == 4) { ?>class="bookme-steps-is-active"<?php } ?>>
        <span><?php echo System::get_translated_option('bookme_lang_step_details') ?></span>
    </li>
    <li <?php if ($step == 5) { ?>class="bookme-steps-is-active"<?php } ?>>
        <span><?php echo System::get_translated_option('bookme_lang_step_done') ?></span>
    </li>
</ul>