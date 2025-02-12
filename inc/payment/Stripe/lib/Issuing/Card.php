<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Issuing;

/**
 * Class Card
 *
 * @property string $id
 * @property string $object
 * @property mixed $authorization_controls
 * @property mixed $billing
 * @property string $brand
 * @property Cardholder $cardholder
 * @property int $created
 * @property string $currency
 * @property int $exp_month
 * @property int $exp_year
 * @property string $last4
 * @property bool $livemode
 * @property \Bookme\Inc\Payment\Stripe\Lib\StripeObject $metadata
 * @property string $name
 * @property mixed $shipping
 * @property string $status
 * @property string $type
 *
 * @package Stripe\Issuing
 */
class Card extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "issuing.card";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Update;

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return CardDetails The card details associated with that issuing card.
     */
    public function details($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/details';
        list($response, $opts) = $this->_request('get', $url, $params, $options);
        $obj = \Bookme\Inc\Payment\Stripe\Lib\Util\Util::convertToStripeObject($response, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }
}
