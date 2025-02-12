<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Checkout;

/**
 * Class Session
 *
 * @property string $id
 * @property string $object
 * @property bool $livemode
 *
 * @package Stripe
 */
class Session extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{

    const OBJECT_NAME = "checkout.session";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
}
