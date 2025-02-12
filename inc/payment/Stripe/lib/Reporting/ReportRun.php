<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Reporting;

/**
 * Class ReportRun
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property string $error
 * @property bool $livemode
 * @property mixed $parameters
 * @property string $report_type
 * @property mixed $result
 * @property string $status
 * @property int $succeeded_at
 *
 * @package Stripe\Reporting
 */
class ReportRun extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "reporting.report_run";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Create;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;
}
