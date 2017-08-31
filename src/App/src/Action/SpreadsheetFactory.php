<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class SpreadsheetFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new SpreadsheetAction();
    }
}