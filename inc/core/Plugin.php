<?php

namespace Bookme\Inc\Core;

use Bookme\Inc;

/**
 * Class Plugin
 */
abstract class Plugin
{
    /**
     * Prefix for options and metas.
     *
     * @staticvar string
     */
    protected static $prefix;

    /**
     * Plugin slug.
     *
     * @staticvar string
     */
    protected static $slug;

    /**
     * Path to plugin main file.
     *
     * @staticvar string
     */
    protected static $main_file;

    /**
     * Plugin basename.
     *
     * @staticvar string
     */
    protected static $basename;

    /**
     * Plugin text domain.
     *
     * @staticvar string
     */
    protected static $text_domain;

    /**
     * Root namespace of plugin classes.
     *
     * @staticvar string
     */
    protected static $root_namespace;

    /**
     * Array of plugin classes for objects.
     *
     * @var static[]
     */
    private static $plugin_classes = array();

    /**
     * Start Bookme plugin.
     */
    public static function run()
    {
        static::register_hooks();

        // Run updates
        /** @var Inc\Mains\Updater $updater */
        $updater_class = static::get_root_namespace() . '\Inc\Mains\Updater';
        $updater = new $updater_class();
        $updater->run();
    }

    /**
     * Register hooks.
     */
    public static function register_hooks()
    {
        /** @var Plugin $plugin_class */
        $plugin_class = get_called_class();

        register_activation_hook(static::get_main_file(), array($plugin_class, 'activate'));
        register_deactivation_hook(static::get_main_file(), array($plugin_class, 'deactivate'));
        register_uninstall_hook(static::get_main_file(), array($plugin_class, 'uninstall'));

        add_action('plugins_loaded', function () use ($plugin_class) {
            // lang
            load_plugin_textdomain($plugin_class::get_text_domain(), false, $plugin_class::get_slug() . '/languages');
        });

        // WordPress daily cron
        if (!wp_next_scheduled('bookme_weekly_task')) {
            wp_schedule_event(time(), 'weekly', 'bookme_weekly_task');
        }
    }

    /**
     * Activate plugin.
     *
     * @param bool $network_wide
     */
    public static function activate($network_wide)
    {
        $installer_class = static::get_root_namespace() . '\Inc\Mains\Installer';
        $installer = new $installer_class();
        $installer->install();
    }

    /**
     * Deactivate plugin.
     *
     * @param bool $network_wide
     */
    public static function deactivate($network_wide)
    {
        unload_textdomain('bookme');
    }

    /**
     * Uninstall plugin.
     *
     * @param string|bool $network_wide
     */
    public static function uninstall($network_wide)
    {
        $installer_class = static::get_root_namespace() . '\Inc\Mains\Installer';
        $installer = new $installer_class();
        $installer->uninstall();

    }

    /**
     * Get prefix.
     *
     * @return mixed
     */
    public static function get_prefix()
    {
        if (static::$prefix === null) {
            static::$prefix = str_replace(array('-addon', '-'), array('', '_'), static::get_slug()) . '_';
        }

        return static::$prefix;
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public static function get_version()
    {
        return BOOKME_VERSION;
    }

    /**
     * Get plugin slug.
     *
     * @return string
     */
    public static function get_slug()
    {
        if (static::$slug === null) {
            static::$slug = basename(static::get_directory());
        }

        return static::$slug;
    }

    /**
     * Get path to plugin directory.
     *
     * @return string
     */
    public static function get_directory()
    {
        return BOOKME_PATH;
    }

    /**
     * Get path to plugin main file.
     *
     * @return string
     */
    public static function get_main_file()
    {
        if (static::$main_file === null) {
            static::$main_file = static::get_directory() . 'init.php';
        }

        return static::$main_file;
    }

    /**
     * Get plugin basename.
     *
     * @return string
     */
    public static function get_basename()
    {
        if (static::$basename === null) {
            static::$basename = plugin_basename(static::get_main_file());
        }

        return static::$basename;
    }

    /**
     * Get plugin text domain.
     *
     * @return string
     */
    public static function get_text_domain()
    {
        if (static::$text_domain === null) {
            if (!function_exists('get_plugin_data')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            $plugin_data = get_plugin_data(static::get_main_file());
            static::$text_domain = $plugin_data['TextDomain'];
        }

        return static::$text_domain;
    }

    /**
     * Get root namespace of called class.
     *
     * @return string
     */
    public static function get_root_namespace()
    {
        if (static::$root_namespace === null) {
            $called_class = get_called_class();
            static::$root_namespace = substr($called_class, 0, strpos($called_class, '\\'));
        }

        return static::$root_namespace;
    }

    /**
     * Get table classes.
     *
     * @return Inc\Core\Table[]
     */
    public static function get_table_classes()
    {
        $result = array();

        $dir = static::get_directory() . '/inc/mains/tables';
        if (is_dir($dir)) {
            foreach (scandir($dir) as $filename) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }
                $result[] = static::get_root_namespace() . '\Inc\Mains\Tables\\' . basename($filename, '.php');
            }
        }

        return $result;
    }

    /**
     * Check whether the plugin is network active.
     *
     * @return bool
     */
    public static function is_network_active()
    {
        return is_plugin_active_for_network(static::get_basename());
    }

    /**
     * Get plugin class for given object.
     *
     * @param $object
     * @return static
     */
    public static function get_plugin_for($object)
    {
        $class = get_class($object);

        if (!isset (self::$plugin_classes[$class])) {
            self::$plugin_classes[$class] = substr($class, 0, strpos($class, '\\')) . '\Inc\Mains\Plugin';
        }

        return self::$plugin_classes[$class];
    }

}