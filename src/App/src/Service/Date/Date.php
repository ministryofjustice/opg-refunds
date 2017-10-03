<?php

namespace App\Service\Date;

/**
 * Class Date
 * @package App\Service\Date
 */
class Date implements IDate
{
    public function getTimeNow(): int
    {
        return time();
    }
}