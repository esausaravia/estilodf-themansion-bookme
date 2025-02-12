<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Radar;

/**
 * Class ValueListItem
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property string $created_by
 * @property string $list
 * @property bool $livemode
 * @property string $value
 *
 * @package Stripe\Radar
 */
class ValueListItem extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "radar.value_list_item";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Delete;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;
}
