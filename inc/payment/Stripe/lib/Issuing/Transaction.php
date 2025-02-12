<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Issuing;

/**
 * Class Transaction
 *
 * @property string $id
 * @property string $object
 * @property int $amount
 * @property string $authorization
 * @property string $balance_transaction
 * @property string $card
 * @property string $cardholder
 * @property int $created
 * @property string $currency
 * @property string $dispute
 * @property bool $livemode
 * @property mixed $merchant_data
 * @property \Bookme\Inc\Payment\Stripe\Lib\StripeObject $metadata
 * @property string $type
 *
 * @package Stripe\Issuing
 */
class Transaction extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "issuing.transaction";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Update;
}
