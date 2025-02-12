<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Terminal;

/**
 * Class ConnectionToken
 *
 * @property string $secret
 *
 * @package Stripe\Terminal
 */
class ConnectionToken extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "terminal.connection_token";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
}
