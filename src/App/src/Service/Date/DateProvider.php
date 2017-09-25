<?php

namespace App\Service\Date;

/**
 * Class DateProvider
 * @package App\Service\Date
 */
class DateProvider implements IDateProvider
{
    public function getTimeNow(): int
    {
        return time();
    }
}
