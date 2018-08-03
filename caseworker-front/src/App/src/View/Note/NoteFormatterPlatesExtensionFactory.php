<?php

namespace App\View\Note;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper;

/**
 * Class NoteFormatterPlatesExtensionFactory
 * @package App\View\Note
 */
class NoteFormatterPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new NoteFormatterPlatesExtension(
            $container->get(UrlHelper::class)
        );
    }
}
