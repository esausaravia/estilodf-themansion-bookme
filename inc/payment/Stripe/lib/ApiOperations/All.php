<?php

namespace Bookme\Inc\Payment\Stripe\Lib\ApiOperations;

/**
 * Trait for listable resources. Adds a `all()` static method to the class.
 *
 * This trait should only be applied to classes that derive from StripeObject.
 */
trait All
{
    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return \Bookme\Inc\Payment\Stripe\Lib\Collection of ApiResources
     */
    public static function all($params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('get', $url, $params, $opts);
        $obj = \Bookme\Inc\Payment\Stripe\Lib\Util\Util::convertToStripeObject($response->json, $opts);
        if (!is_a($obj, 'Stripe\\Collection')) {
            $class = get_class($obj);
            $message = "Expected type \"Stripe\\Collection\", got \"$class\" instead";
            throw new \Bookme\Inc\Payment\Stripe\Lib\Error\Api($message);
        }
        $obj->setLastResponse($response);
        $obj->setRequestParams($params);
        return $obj;
    }
}
