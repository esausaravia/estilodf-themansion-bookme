<?php

namespace Bookme\Inc\Payment\Stripe\Lib;

/**
 * Class Review
 *
 * @property string $id
 * @property string $object
 * @property string $billing_zip
 * @property string $charge
 * @property string $closed_reason
 * @property int $created
 * @property string $ip_address
 * @property mixed $ip_address_location
 * @property bool $livemode
 * @property bool $open
 * @property string $opened_reason
 * @property string $payment_intent
 * @property string $reason
 * @property mixed $session
 *
 * @package Stripe
 */
class Review extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "review";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;

    /**
     * @param array|string|null $options
     *
     * @return Review The approved review.
     */
    public function approve($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/approve';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
