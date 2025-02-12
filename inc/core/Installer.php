<?php
namespace Bookme\Inc\Core;

/**
 * Class Installer
 */
abstract class Installer extends Schema
{
    protected $options = array();

    /**
     * Install.
     */
    public function install()
    {
        $plugin_class = Plugin::get_plugin_for( $this );
        $data_loaded_option_name = $plugin_class::get_prefix() . 'data_loaded';

        // Create tables and load data if it hasn't been loaded yet.
        if ( ! get_option( $data_loaded_option_name ) ) {
            $this->create_tables();
            $this->load_data();
        }

        update_option( $data_loaded_option_name, '1' );
    }

    /**
     * Uninstall.
     */
    public function uninstall()
    {
        $this->remove_lang_data();
        wp_clear_scheduled_hook('bookme_weekly_task');
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function get_options()
    {
        return $this->options;
    }

    /**
     * Create tables.
     */
    public function create_tables(){}

    /**
     * Drop tables
     */
    public function drop_tables()
    {
        $this->drop_plugin_tables();
    }

    /**
     * Load data.
     */
    public function load_data()
    {
        // Add default options.
        $plugin_class  = Plugin::get_plugin_for( $this );
        $plugin_prefix = $plugin_class::get_prefix();
        update_option( $plugin_prefix . 'data_loaded', '0' );
        update_option( $plugin_prefix . 'db_version',  $plugin_class::get_version() );

        // Add plugin options.
        foreach ( $this->options as $name => $value ) {
            update_option( $name, $value );
            if ( strpos( $name, 'bookme_lang_' ) === 0 ) {
                do_action( 'wpml_register_single_string', 'bookme', $name, $value );
            }
        }
    }

    /**
     * Remove lang data.
     */
    protected function remove_lang_data()
    {
        global $wpdb;
        $wpml_strings_table = $wpdb->prefix . 'icl_strings';
        $result = $wpdb->query("SELECT table_name FROM information_schema.tables WHERE table_name = '$wpml_strings_table' AND TABLE_SCHEMA=SCHEMA()");
        if ($result == 1) {
            @$wpdb->query("DELETE FROM {$wpdb->prefix}icl_string_translations WHERE string_id IN (SELECT id FROM $wpml_strings_table WHERE context='bookme')");
            @$wpdb->query("DELETE FROM {$wpdb->prefix}icl_string_positions WHERE string_id IN (SELECT id FROM $wpml_strings_table WHERE context='bookme')");
            @$wpdb->query("DELETE FROM {$wpml_strings_table} WHERE context='bookme'");
        }
    }

}