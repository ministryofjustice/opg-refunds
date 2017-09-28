<?php

namespace App\Service\Date;

/**
 * Interface IDateProvider
 * @package App\Service\Date
 */
interface IDateProvider
{
    public function getTimeNow(): int;
}
