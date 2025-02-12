<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Terminal;

/**
 * Class Location
 *
 * @property string $id
 * @property string $object
 * @property string $display_name
 * @property string $address_city
 * @property string $address_country
 * @property string $address_line1
 * @property string $address_line2
 * @property string $address_state
 * @property string $address_postal_code
 *
 * @package Stripe\Terminal
 */
class Location extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "terminal.location";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Update;
}
