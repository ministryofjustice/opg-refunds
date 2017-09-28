<?php
namespace App\Service\ErrorMapper;

use Interop\Container\ContainerInterface;

class ErrorMapperFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ErrorMapper;
    }
}
