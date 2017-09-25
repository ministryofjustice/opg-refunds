<?php

use Zend\Expressive\Plates\PlatesRendererFactory;
use Zend\Expressive\Template\TemplateRendererInterface;

return [
    'dependencies' => [
        'factories' => [
            TemplateRendererInterface::class => PlatesRendererFactory::class,
        ],
    ],

    'templates' => [
        'extension' => 'phtml',
    ],

    'plates' => [
        'extensions' => [
            App\Service\Date\DateFormatterPlatesExtension::class,
            App\Service\Details\DetailsFormatterPlatesExtension::class,
            App\Service\ErrorMapper\ErrorMapperPlatesExtension::class
        ],
    ],
];
