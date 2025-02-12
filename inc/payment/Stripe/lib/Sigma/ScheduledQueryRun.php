<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Sigma;

/**
 * Class Authorization
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property int $data_load_time
 * @property string $error
 * @property \Bookme\Inc\Payment\Stripe\Lib\FileUpload $file
 * @property bool $livemode
 * @property int $result_available_until
 * @property string $sql
 * @property string $status
 * @property string $title
 *
 * @package Stripe\Sigma
 */
class ScheduledQueryRun extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "scheduled_query_run";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;

    public static function classUrl()
    {
        return "/v1/sigma/scheduled_query_runs";
    }
}
