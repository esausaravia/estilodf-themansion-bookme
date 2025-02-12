<?php

namespace Bookme\Inc\Core;

/**
 * Class Updater
 */
abstract class Updater extends Schema
{
    /**
     * Get table name.
     *
     * @param string $table
     * @return string
     */
    protected function get_table_name($table)
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        return $wpdb->prefix . $table;
    }

    /**
     * Execute array queries where the key is the table name.
     *
     * @param array $data key is table name
     */
    protected function alter_tables(array $data)
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        foreach ($data as $table => $queries) {
            $table_name = $this->get_table_name($table);
            foreach ($queries as $query) {
                $wpdb->query(sprintf($query, $table_name));
            }
        }
    }

    /**
     * Rename options.
     *
     * @param array $options
     */
    protected function rename_options(array $options)
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        foreach ($options as $old_name => $new_name) {
            $wpdb->query($wpdb->prepare(
                'UPDATE `' . $wpdb->options . '` SET `option_name` = %s WHERE `option_name` = %s',
                $new_name,
                $old_name
            ));
        }
    }

    /**
     * Add options and register corresponding WPML strings.
     *
     * @param array $options
     */
    protected function add_lang_options(array $options)
    {
        foreach ($options as $option_name => $option_value) {
            add_option($option_name, $option_value);
            do_action('wpml_register_single_string', 'bookme', $option_name, $option_value);
        }
    }

    /**
     * Rename WPML strings.
     *
     * @param array $strings
     * @param bool $rename_options
     */
    protected function rename_lang_strings(array $strings, $rename_options = true)
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $wpml_strings_table = $this->get_table_name('icl_strings');

        // Check that WPML table with strings exist.
        $exists = $wpdb->query($wpdb->prepare(
            'SELECT 1 FROM `information_schema`.`tables`
                WHERE `table_name`   = %s
                  AND `table_schema` = SCHEMA()
                LIMIT 1',
            $wpml_strings_table
        ));
        if ($exists) {
            // Check that `domain_name_context_md5` column exists.
            $exists = $wpdb->query($wpdb->prepare(
                'SELECT 1 FROM `information_schema`.`columns`
                    WHERE `column_name`  = "domain_name_context_md5"
                      AND `table_name`   = %s
                      AND `table_schema` = SCHEMA()
                    LIMIT 1',
                $wpml_strings_table
            ));
            if ($exists) {
                foreach ($strings as $old_name => $new_name) {
                    $wpdb->query($wpdb->prepare(
                        "UPDATE `$wpml_strings_table`
                          SET `name` = %s, `domain_name_context_md5` = MD5(CONCAT(`context`, %s, `gettext_context`))
                          WHERE `name` = %s",
                        $new_name,
                        $new_name,
                        $old_name
                    ));
                }
            } else {
                foreach ($strings as $old_name => $new_name) {
                    $wpdb->query($wpdb->prepare(
                        "UPDATE `$wpml_strings_table` SET `name` = %s WHERE `name` = %s",
                        $new_name,
                        $old_name
                    ));
                }
            }
        }

        if ($rename_options) {
            $this->rename_options($strings);
        }
    }

    /**
     * Run updates on 'plugins_loaded' hook.
     */
    public function run()
    {
        $updater = $this;
        add_action('plugins_loaded', function () use ($updater) {
            $plugin_class = Plugin::get_plugin_for($updater);
            $version_option_name = $plugin_class::get_prefix() . 'db_version';
            $db_version = get_option($version_option_name);
            $plugin_version = $plugin_class::get_version();

            if ($db_version !== false && version_compare($plugin_version, $db_version, '>')) {
                set_time_limit(0);

                $updates = array_filter(
                    get_class_methods($updater),
                    function ($method) {
                        return strstr($method, 'version_');
                    }
                );
                usort($updates, 'strnatcmp');

                foreach ($updates as $method) {
                    $version = str_replace('_', '.', substr($method, 8));
                    if (strnatcmp($version, $db_version) > 0 && strnatcmp($version, $plugin_version) <= 0) {
                        call_user_func(array($updater, $method));
                        update_option($version_option_name, $version);
                    }
                }
            }

            // hack for calling old bookme backup function
            if(!(int)get_option('bookme_processed_backup', 0)){
                call_user_func(array($updater, 'version_4_0'));
            }
        });
    }
}