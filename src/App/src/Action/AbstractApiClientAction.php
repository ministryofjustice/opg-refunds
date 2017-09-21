<?php

namespace App\Action;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;

/**
 * Class AbstractApiClientAction
 * @package App\Action
 */
abstract class AbstractApiClientAction extends AbstractAction implements ApiClientInterface
{
    use ApiClientTrait;
}
