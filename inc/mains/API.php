<?php
namespace Bookme\Inc\Mains;

/**
 * Class API
 */
abstract class API
{
    const API_URL = 'https://bylancer.com/api/api.php';

    /**
     * Check Bookme purchase code
     * @param $purchase_code
     * @return array|bool|mixed|null|object
     */
    public static function check_purchase_code($purchase_code)
    {
        $url = self::API_URL . "?verify-purchase=" . $purchase_code . "&version=" . BOOKME_VERSION . "&site_url=" . get_bloginfo('url') . "&email=" . get_bloginfo('admin_email');
        $response = wp_remote_fopen($url);
        if ($response) {
            return json_decode($response, true);
        }
        return false;
    }
}