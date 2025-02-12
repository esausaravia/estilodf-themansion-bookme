<?php
namespace Bookme\App\Admin;

use Bookme\Inc;
/**
 * Class ShortcodeButton
 */
class ShortcodeButton {

    public function __construct()
    {
        global $PHP_SELF;
        if ( // check if we are in admin area and current page is adding/editing the post
            is_admin() && (strpos($PHP_SELF, 'post-new.php') !== false || strpos($PHP_SELF, 'post.php') !== false || strpos($PHP_SELF, 'admin-ajax.php'))
        ) {
            // for elementor page builder
            add_action('elementor/editor/footer', array($this,'render_popup'));
            add_action('admin_footer', array($this, 'render_popup'));
            add_filter('media_buttons', array($this, 'add_button'), 50);

        }
    }

    public function add_button($editor_id)
    {
        // don't show on dashboard (QuickPress)
        $current_screen = get_current_screen();
        if ($current_screen && 'dashboard' == $current_screen->base) {
            return;
        }

        // don't display button for users who don't have access
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }

        // display buttons
        echo '<a href="#TB_inline?width=640&inlineId=bookme-tinymce-popup&height=650" id="add-bookme-form" class="thickbox button bookme-media-button" title="' . esc_attr__('Add Bookme Form', 'bookme') . '"><span class="bookme-media-icon"></span> ' . esc_html__('Bookme Form', 'bookme') . '</a>';

        echo '<a href="#TB_inline?width=640&inlineId=bookme-tinymce-bookings-popup&height=650" id="add-bookme-bookings" class="thickbox button bookme-media-button" title="' . esc_attr__('Add Bookme Customer Bookings List', 'bookme') . '"><span class="bookme-media-icon"></span> ' . esc_html__('Bookme Customer Bookings', 'bookme') . '</a>';

    }

    public function render_popup()
    {
        $data = Inc\Mains\Functions\System::get_categories_services_staffs();

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

        Inc\Core\Template::create('shortcode_button/page')->display(compact('data'));
    }
}