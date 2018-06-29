<?php

namespace App\Service\Authentication;

use Zend\Authentication\Result as ZendResult;

class Result extends ZendResult
{
    const FAILURE_ACCOUNT_LOCKED = -403;
}