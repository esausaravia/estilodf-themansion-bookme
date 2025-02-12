<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Terminal;

/**
 * Class Reader
 *
 * @property string $id
 * @property string $object
 * @property string $device_type
 * @property string $serial_number
 * @property string $label
 * @property string $ip_address
 *
 * @package Stripe\Terminal
 */
class Reader extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "terminal.reader";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Update;
}
