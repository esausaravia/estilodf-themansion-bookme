<?php

namespace Bookme\Inc\Payment\Stripe\Lib;

/**
 * Class ExchangeRate
 *
 * @package Stripe
 */
class ExchangeRate extends ApiResource
{

    const OBJECT_NAME = "exchange_rate";

    use ApiOperations\All;
    use ApiOperations\Retrieve;
}
