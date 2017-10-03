<?php

namespace App\Service\Date;

/**
 * Interface IDate
 * @package App\Service\Date
 */
interface IDate
{
    public function getTimeNow(): int;
}