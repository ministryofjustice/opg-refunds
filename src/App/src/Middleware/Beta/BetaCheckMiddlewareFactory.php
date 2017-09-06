<?php
namespace App\Middleware\Beta;

use Interop\Container\ContainerInterface;

use Zend\Expressive\Template\TemplateRendererInterface;

class BetaCheckMiddlewareFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['beta']['cookie']['name'])) {
            throw new \UnexpectedValueException('Beta cookie name not configured');
        }

        if (!isset($config['beta']['enabled'])) {
            throw new \UnexpectedValueException('Beta enabled not configured');
        }

        //---

        return new BetaCheckMiddleware(
            $container->get(TemplateRendererInterface::class),
            $container->get(\App\Service\Refund\Beta\BetaLinkChecker::class),
            $config['beta']['cookie']['name'],
            $config['beta']['enabled']
        );
    }

}