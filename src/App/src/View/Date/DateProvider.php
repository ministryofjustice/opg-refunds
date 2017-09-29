<?php

namespace App\View\Date;

/**
 * Class DateProvider
 * @package App\View\Date
 */
class DateProvider implements IDateProvider
{
    public function getTimeNow(): int
    {
        return time();
    }
}
