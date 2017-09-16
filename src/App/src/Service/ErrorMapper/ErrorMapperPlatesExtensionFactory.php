<?php
namespace App\Service\ErrorMapper;

use Interop\Container\ContainerInterface;

class ErrorMapperPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ErrorMapperPlatesExtension(
            $container->get(ErrorMapper::class)
        );
    }
}
