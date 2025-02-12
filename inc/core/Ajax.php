<?php
namespace Bookme\Inc\Core;

use Bookme\Inc;
/**
 * Class Ajax
 */
abstract class Ajax
{

    /**
     * Register WP Ajax actions with add_action() function
     * based on public 'perform_*' methods of the class.
     *
     * @param $class
     * @param array $permissions permissions for users
     * @param array $exclude_actions exclude actions from security token check
     * @param bool $with_nopriv Whether to register 'wp_ajax_nopriv_' actions too
     */
    public static function register_ajax_actions($class, $permissions = array(), $exclude_actions = array(), $with_nopriv = false)
    {
        $plugin_class = Inc\Mains\Plugin::get_plugin_for($class);

        // Prefixes for auto generated add_action() $tag parameter.
        $prefix = sprintf('wp_ajax_%s', $plugin_class::get_prefix());
        if ($with_nopriv) {
            $nopriv_prefix = sprintf('wp_ajax_nopriv_%s', $plugin_class::get_prefix());
        }

        foreach (get_class_methods($class) as $method) {
            if (preg_match('/^perform_(.*)/', $method, $match)) {
                $action = $match[1];
                $function = function () use ($class, $permissions, $exclude_actions, $match) {
                    if (self::security_token_valid($match[0], $exclude_actions) && self::has_access($match[0], $permissions)) {
                        date_default_timezone_set('UTC');
                        // call the function
                        call_user_func(array($class, $match[0]));
                    } else {
                        wp_die('Bookme: ' . __('You do not have sufficient permissions to access this page.'));
                    }
                };
                add_action($prefix . $action, $function);
                if ($with_nopriv) {
                    add_action($nopriv_prefix . $action, $function);
                }
            }
        }
    }

    /**
     * TODO: add an advanced access checking method for each app's ajax
     * Check if the current user has access to the action.
     *
     * Default access (if is not set in getPermissions for controller or action) is "admin"
     * Access type:
     *  "admin"     - check if the current user is admin
     *  "user"      - check if the current user is authenticated
     *  "everyone"  - everyone
     *
     * @param string $action
     * @param $permissions
     * Array structure:
     *  [
     *    <method_name> => Access for specific action
     *    _this         => Default access for controller actions
     *  ]
     *
     * @return bool
     */
    protected static function has_access($action, $permissions)
    {
        $security = isset ($permissions[$action]) ? $permissions[$action] : null;

        if (is_null($security)) {
            // Check if app class has permission.
            $security = isset ($permissions['app']) ? $permissions['app'] : 'admin';
        }

        switch ($security) {
            case 'admin':
                return Inc\Mains\Functions\System::is_current_user_admin();
            case 'user':
                return is_user_logged_in();
            case 'everyone':
                return true;
        }

        return false;
    }

    /**
     * Verify Security token.
     *
     * @param string $action
     * @param array $exclude_actions
     * @return bool
     */
    protected static function security_token_valid($action, $exclude_actions = array())
    {
        return in_array($action, $exclude_actions) || wp_verify_nonce(Inc\Mains\Functions\Request::get_parameter('csrf_token'), 'bookme') == 1;
    }
}