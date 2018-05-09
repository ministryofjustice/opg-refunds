<?php

namespace OpgTest\Refunds\Caseworker\DataModel;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use PHPUnit\Framework\TestCase;
use DateTime;

class AbstractDataModelTestCase extends TestCase
{
    protected function dateTimeToString(DateTime $dateTime)
    {
        return $dateTime->format(AbstractDataModel::DATE_TIME_STRING_FORMAT);
    }
}
