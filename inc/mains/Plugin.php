<?php
namespace Bookme\Inc\Mains;

use Bookme\Inc;

/**
 * Class Plugin
 */
abstract class Plugin extends Inc\Core\Plugin
{
    protected static $prefix = 'bookme_';
    protected static $title;
    protected static $version;
    protected static $slug;
    protected static $directory;
    protected static $main_file;
    protected static $basename;
    protected static $text_domain;
    protected static $root_namespace;
    protected static $embedded;

    public static function register_hooks()
    {
        parent::register_hooks();

        if (is_admin()) {
            add_action('admin_notices', function () {
                $bookme_page = isset($_REQUEST['page']) && strpos($_REQUEST['page'], 'bookme-') === 0;
                if ($bookme_page) {
                    //if (!(get_option('bookme_secret_file') && get_option('bookme_purchase_code'))) {
                        ?>

                        <?php
                    //}
                }
            }, 10, 0);
        }

        add_action('bookme_weekly_task', function () {
            $staff_path = BOOKME_PATH . '/templates/admin/employees/';
            if ($code = get_option('bookme_purchase_code')) {
                if ($result = API::check_purchase_code($code)) {
                    if (!$result['success']) {
                        if ($already_file = get_option('bookme_secret_file')) {
                            if (file_exists($staff_path . $already_file . '.php')) {
                                unlink($staff_path . $already_file . '.php');
                                update_option('bookme_secret_file', '');
                            }
                        }
                    }
                }
            } else {
                if ($already_file = get_option('bookme_secret_file')) {
                    if (file_exists($staff_path . $already_file . '.php')) {
                        unlink($staff_path . $already_file . '.php');
                        update_option('bookme_secret_file', '');
                    }
                }
            }
        }, 10, 0);
    }

}