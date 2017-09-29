<?php

namespace App\View\Date;

/**
 * Interface IDateProvider
 * @package App\View\Date
 */
interface IDateProvider
{
    public function getTimeNow(): int;
}
