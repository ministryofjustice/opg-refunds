<?php

namespace App\View\Url;

use Interop\Container\ContainerInterface;
use Mezzio\Helper\UrlHelper;

/**
 * Class UrlHelperPlatesExtensionFactory
 * @package App\View\Url
 */
class UrlHelperPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new UrlHelperPlatesExtension(
            $container->get(UrlHelper::class)
        );
    }
}
