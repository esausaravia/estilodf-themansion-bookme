<?php

namespace Bookme\Inc\Payment\Stripe\Lib\Reporting;

/**
 * Class ReportType
 *
 * @property string $id
 * @property string $object
 * @property int $data_available_end
 * @property int $data_available_start
 * @property string $name
 * @property int $updated
 * @property string $version
 *
 * @package Stripe\Reporting
 */
class ReportType extends \Bookme\Inc\Payment\Stripe\Lib\ApiResource
{
    const OBJECT_NAME = "reporting.report_type";

    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\All;
    use \Bookme\Inc\Payment\Stripe\Lib\ApiOperations\Retrieve;
}
