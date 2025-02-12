<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Issuing;

/**
 * Class Dispute
 *
 * @property string $id
 * @property string $object
 * @property int $amount
 * @property int $created
 * @property string $currency
 * @property mixed $evidence
 * @property bool $livemode
 * @property \Bookme\Inc\Payment\Stripe\Lib\StripeObject $metadata
 * @property string $reason
 * @property string $status
 * @property Transaction $transaction
 *
 * @package Stripe\Issuing
 */
class Dispute extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "issuing.dispute";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Update;
}
