<?php

namespace App\Service\Authentication;

use Laminas\Authentication\Result as LaminasResult;

class Result extends LaminasResult
{
    const FAILURE_ACCOUNT_LOCKED = -403;
}
