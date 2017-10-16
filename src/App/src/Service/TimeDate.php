<?php

namespace App\Service;

use DateTime;

/**
 * Service for returning common DateTime instances. Can be mocked for unit testing
 *
 * Class Date
 * @package App\Service
 */
class TimeDate
{
    public function getTimeNow(): int
    {
        return time();
    }

    public function getDateTimeNow(): DateTime
    {
        return new DateTime();
    }

    public function getDateTimeToday(): DateTime
    {
        return new DateTime('today');
    }
}