<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Bookme autoload.
 * @param $class
 */
function bookme_loader( $class )
{
    if ( preg_match( '/^Bookme\\\\(.+)?([^\\\\]+)$/U', ltrim( $class, '\\' ), $match ) ) {
        $file = BOOKME_PATH
                . strtolower( str_replace( '\\', DIRECTORY_SEPARATOR, preg_replace( '/([a-z])([A-Z])/', '$1_$2', $match[1] ) ) )
                . $match[2]
                . '.php';

        if ( is_readable( $file ) ) {
            require_once $file;
        }
    }
}
spl_autoload_register( 'bookme_loader', true, true );