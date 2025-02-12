<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Util;

use Bookme\Inc\Payment\Stripe\Lib\StripeObject;

abstract class Util
{
    private static $isMbstringAvailable = null;
    private static $isHashEqualsAvailable = null;

    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     * A list is defined as an array for which all the keys are consecutive
     * integers starting at 0. Empty arrays are considered to be lists.
     *
     * @param array|mixed $array
     * @return boolean true if the given object is a list.
     */
    public static function isList($array)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($array === []) {
            return true;
        }
        if (array_keys($array) !== range(0, count($array) - 1)) {
            return false;
        }
        return true;
    }

    /**
     * Recursively converts the PHP Stripe object to an array.
     *
     * @param array $values The PHP Stripe object to convert.
     * @return array
     */
    public static function convertStripeObjectToArray($values)
    {
        $results = [];
        foreach ($values as $k => $v) {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof StripeObject) {
                $results[$k] = $v->__toArray(true);
            } elseif (is_array($v)) {
                $results[$k] = self::convertStripeObjectToArray($v);
            } else {
                $results[$k] = $v;
            }
        }
        return $results;
    }

    /**
     * Converts a response from the Stripe API to the corresponding PHP object.
     *
     * @param array $resp The response from the Stripe API.
     * @param array $opts
     * @return StripeObject|array
     */
    public static function convertToStripeObject($resp, $opts)
    {
        $types = [
            // data structures
            \Bookme\Inc\Payment\Stripe\Lib\Collection::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Collection',

            // business objects
            \Bookme\Inc\Payment\Stripe\Lib\Account::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Account',
            \Bookme\Inc\Payment\Stripe\Lib\AccountLink::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\AccountLink',
            \Bookme\Inc\Payment\Stripe\Lib\AlipayAccount::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\AlipayAccount',
            \Bookme\Inc\Payment\Stripe\Lib\ApplePayDomain::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\ApplePayDomain',
            \Bookme\Inc\Payment\Stripe\Lib\ApplicationFee::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\ApplicationFee',
            \Bookme\Inc\Payment\Stripe\Lib\Balance::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Balance',
            \Bookme\Inc\Payment\Stripe\Lib\BalanceTransaction::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\BalanceTransaction',
            \Bookme\Inc\Payment\Stripe\Lib\BankAccount::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\BankAccount',
            \Bookme\Inc\Payment\Stripe\Lib\BitcoinReceiver::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\BitcoinReceiver',
            \Bookme\Inc\Payment\Stripe\Lib\BitcoinTransaction::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\BitcoinTransaction',
            \Bookme\Inc\Payment\Stripe\Lib\Card::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Card',
            \Bookme\Inc\Payment\Stripe\Lib\Charge::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Charge',
            \Bookme\Inc\Payment\Stripe\Lib\Checkout\Session::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Checkout\\Session',
            \Bookme\Inc\Payment\Stripe\Lib\CountrySpec::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\CountrySpec',
            \Bookme\Inc\Payment\Stripe\Lib\Coupon::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Coupon',
            \Bookme\Inc\Payment\Stripe\Lib\Customer::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Customer',
            \Bookme\Inc\Payment\Stripe\Lib\Discount::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Discount',
            \Bookme\Inc\Payment\Stripe\Lib\Dispute::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Dispute',
            \Bookme\Inc\Payment\Stripe\Lib\EphemeralKey::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\EphemeralKey',
            \Bookme\Inc\Payment\Stripe\Lib\Event::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Event',
            \Bookme\Inc\Payment\Stripe\Lib\ExchangeRate::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\ExchangeRate',
            \Bookme\Inc\Payment\Stripe\Lib\ApplicationFeeRefund::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\ApplicationFeeRefund',
            \Bookme\Inc\Payment\Stripe\Lib\File::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\File',
            \Bookme\Inc\Payment\Stripe\Lib\File::OBJECT_NAME_ALT => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\File',
            \Bookme\Inc\Payment\Stripe\Lib\FileLink::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\FileLink',
            \Bookme\Inc\Payment\Stripe\Lib\Invoice::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Invoice',
            \Bookme\Inc\Payment\Stripe\Lib\InvoiceItem::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\InvoiceItem',
            \Bookme\Inc\Payment\Stripe\Lib\InvoiceLineItem::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\InvoiceLineItem',
            \Bookme\Inc\Payment\Stripe\Lib\IssuerFraudRecord::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\IssuerFraudRecord',
            \Bookme\Inc\Payment\Stripe\Lib\Issuing\Authorization::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Issuing\\Authorization',
            \Bookme\Inc\Payment\Stripe\Lib\Issuing\Card::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Issuing\\Card',
            \Bookme\Inc\Payment\Stripe\Lib\Issuing\CardDetails::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Issuing\\CardDetails',
            \Bookme\Inc\Payment\Stripe\Lib\Issuing\Cardholder::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Issuing\\Cardholder',
            \Bookme\Inc\Payment\Stripe\Lib\Issuing\Dispute::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Issuing\\Dispute',
            \Bookme\Inc\Payment\Stripe\Lib\Issuing\Transaction::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Issuing\\Transaction',
            \Bookme\Inc\Payment\Stripe\Lib\LoginLink::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\LoginLink',
            \Bookme\Inc\Payment\Stripe\Lib\Order::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Order',
            \Bookme\Inc\Payment\Stripe\Lib\OrderItem::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\OrderItem',
            \Bookme\Inc\Payment\Stripe\Lib\OrderReturn::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\OrderReturn',
            \Bookme\Inc\Payment\Stripe\Lib\PaymentIntent::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\PaymentIntent',
            \Bookme\Inc\Payment\Stripe\Lib\Payout::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Payout',
            \Bookme\Inc\Payment\Stripe\Lib\Person::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Person',
            \Bookme\Inc\Payment\Stripe\Lib\Plan::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Plan',
            \Bookme\Inc\Payment\Stripe\Lib\Product::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Product',
            \Bookme\Inc\Payment\Stripe\Lib\Radar\ValueList::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Radar\\ValueList',
            \Bookme\Inc\Payment\Stripe\Lib\Radar\ValueListItem::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Radar\\ValueListItem',
            \Bookme\Inc\Payment\Stripe\Lib\Recipient::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Recipient',
            \Bookme\Inc\Payment\Stripe\Lib\RecipientTransfer::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\RecipientTransfer',
            \Bookme\Inc\Payment\Stripe\Lib\Refund::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Refund',
            \Bookme\Inc\Payment\Stripe\Lib\Reporting\ReportRun::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Reporting\\ReportRun',
            \Bookme\Inc\Payment\Stripe\Lib\Reporting\ReportType::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Reporting\\ReportType',
            \Bookme\Inc\Payment\Stripe\Lib\Review::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Review',
            \Bookme\Inc\Payment\Stripe\Lib\SKU::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\SKU',
            \Bookme\Inc\Payment\Stripe\Lib\Sigma\ScheduledQueryRun::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Sigma\\ScheduledQueryRun',
            \Bookme\Inc\Payment\Stripe\Lib\Source::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Source',
            \Bookme\Inc\Payment\Stripe\Lib\SourceTransaction::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\SourceTransaction',
            \Bookme\Inc\Payment\Stripe\Lib\Subscription::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Subscription',
            \Bookme\Inc\Payment\Stripe\Lib\SubscriptionItem::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\SubscriptionItem',
            \Bookme\Inc\Payment\Stripe\Lib\SubscriptionSchedule::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\SubscriptionSchedule',
            \Bookme\Inc\Payment\Stripe\Lib\SubscriptionScheduleRevision::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\SubscriptionScheduleRevision',
            \Bookme\Inc\Payment\Stripe\Lib\ThreeDSecure::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\ThreeDSecure',
            \Bookme\Inc\Payment\Stripe\Lib\Terminal\ConnectionToken::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Terminal\\ConnectionToken',
            \Bookme\Inc\Payment\Stripe\Lib\Terminal\Location::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Terminal\\Location',
            \Bookme\Inc\Payment\Stripe\Lib\Terminal\Reader::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Terminal\\Reader',
            \Bookme\Inc\Payment\Stripe\Lib\Token::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Token',
            \Bookme\Inc\Payment\Stripe\Lib\Topup::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Topup',
            \Bookme\Inc\Payment\Stripe\Lib\Transfer::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\Transfer',
            \Bookme\Inc\Payment\Stripe\Lib\TransferReversal::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\TransferReversal',
            \Bookme\Inc\Payment\Stripe\Lib\UsageRecord::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\UsageRecord',
            \Bookme\Inc\Payment\Stripe\Lib\UsageRecordSummary::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\UsageRecordSummary',
            \Bookme\Inc\Payment\Stripe\Lib\WebhookEndpoint::OBJECT_NAME => 'Bookme\\Inc\\Payment\\Stripe\\Lib\\WebhookEndpoint',
        ];
        if (self::isList($resp)) {
            $mapped = [];
            foreach ($resp as $i) {
                array_push($mapped, self::convertToStripeObject($i, $opts));
            }
            return $mapped;
        } elseif (is_array($resp)) {
            if (isset($resp['object']) && is_string($resp['object']) && isset($types[$resp['object']])) {
                $class = $types[$resp['object']];
            } else {
                $class = 'Bookme\\Inc\\Payment\\Stripe\\Lib\\StripeObject';
            }
            return $class::constructFrom($resp, $opts);
        } else {
            return $resp;
        }
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @return string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if (self::$isMbstringAvailable === null) {
            self::$isMbstringAvailable = function_exists('mb_detect_encoding');

            if (!self::$isMbstringAvailable) {
                trigger_error("It looks like the mbstring extension is not enabled. " .
                    "UTF-8 strings will not properly be encoded. Ask your system " .
                    "administrator to enable the mbstring extension, or write to " .
                    "support@stripe.com if you have any questions.", E_USER_WARNING);
            }
        }

        if (is_string($value) && self::$isMbstringAvailable && mb_detect_encoding($value, "UTF-8", true) != "UTF-8") {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }

    /**
     * Compares two strings for equality. The time taken is independent of the
     * number of characters that match.
     *
     * @param string $a one of the strings to compare.
     * @param string $b the other string to compare.
     * @return bool true if the strings are equal, false otherwise.
     */
    public static function secureCompare($a, $b)
    {
        if (self::$isHashEqualsAvailable === null) {
            self::$isHashEqualsAvailable = function_exists('hash_equals');
        }

        if (self::$isHashEqualsAvailable) {
            return hash_equals($a, $b);
        } else {
            if (strlen($a) != strlen($b)) {
                return false;
            }

            $result = 0;
            for ($i = 0; $i < strlen($a); $i++) {
                $result |= ord($a[$i]) ^ ord($b[$i]);
            }
            return ($result == 0);
        }
    }

    /**
     * Recursively goes through an array of parameters. If a parameter is an instance of
     * ApiResource, then it is replaced by the resource's ID.
     * Also clears out null values.
     *
     * @param mixed $h
     * @return mixed
     */
    public static function objectsToIds($h)
    {
        if ($h instanceof \Bookme\Inc\Payment\Stripe\Lib\ApiResource) {
            return $h->id;
        } elseif (static::isList($h)) {
            $results = [];
            foreach ($h as $v) {
                array_push($results, static::objectsToIds($v));
            }
            return $results;
        } elseif (is_array($h)) {
            $results = [];
            foreach ($h as $k => $v) {
                if (is_null($v)) {
                    continue;
                }
                $results[$k] = static::objectsToIds($v);
            }
            return $results;
        } else {
            return $h;
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public static function encodeParameters($params)
    {
        $flattenedParams = self::flattenParams($params);
        $pieces = [];
        foreach ($flattenedParams as $param) {
            list($k, $v) = $param;
            array_push($pieces, self::urlEncode($k) . '=' . self::urlEncode($v));
        }
        return implode('&', $pieces);
    }

    /**
     * @param array $params
     * @param string|null $parentKey
     *
     * @return array
     */
    public static function flattenParams($params, $parentKey = null)
    {
        $result = [];

        foreach ($params as $key => $value) {
            $calculatedKey = $parentKey ? "{$parentKey}[{$key}]" : $key;

            if (self::isList($value)) {
                $result = array_merge($result, self::flattenParamsList($value, $calculatedKey));
            } elseif (is_array($value)) {
                $result = array_merge($result, self::flattenParams($value, $calculatedKey));
            } else {
                array_push($result, [$calculatedKey, $value]);
            }
        }

        return $result;
    }

    /**
     * @param array $value
     * @param string $calculatedKey
     *
     * @return array
     */
    public static function flattenParamsList($value, $calculatedKey)
    {
        $result = [];

        foreach ($value as $i => $elem) {
            if (self::isList($elem)) {
                $result = array_merge($result, self::flattenParamsList($elem, $calculatedKey));
            } elseif (is_array($elem)) {
                $result = array_merge($result, self::flattenParams($elem, "{$calculatedKey}[{$i}]"));
            } else {
                array_push($result, ["{$calculatedKey}[{$i}]", $elem]);
            }
        }

        return $result;
    }

    /**
     * @param string $key A string to URL-encode.
     *
     * @return string The URL-encoded string.
     */
    public static function urlEncode($key)
    {
        $s = urlencode($key);

        // Don't use strict form encoding by changing the square bracket control
        // characters back to their literals. This is fine by the server, and
        // makes these parameter strings easier to read.
        $s = str_replace('%5B', '[', $s);
        $s = str_replace('%5D', ']', $s);

        return $s;
    }

    public static function normalizeId($id)
    {
        if (is_array($id)) {
            $params = $id;
            $id = $params['id'];
            unset($params['id']);
        } else {
            $params = [];
        }
        return [$id, $params];
    }

    /**
     * Returns UNIX timestamp in milliseconds
     *
     * @return integer current time in millis
     */
    public static function currentTimeMillis()
    {
        return (int) round(microtime(true) * 1000);
    }
}
