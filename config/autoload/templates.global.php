<?php

use League\Plates\Engine as PlatesEngine;
use Zend\Expressive\Plates\PlatesEngineFactory;
use Zend\Expressive\Plates\PlatesRendererFactory;
use Zend\Expressive\Template\TemplateRendererInterface;

return [
    'dependencies' => [
        'factories' => [
            PlatesEngine::class => PlatesEngineFactory::class,
            TemplateRendererInterface::class => PlatesRendererFactory::class,
        ],
    ],

    'templates' => [
        'extension' => 'phtml',
    ],

    'plates' => [
        'extensions' => [
            App\Service\AssetsCache\AssetsCachePlatesExtension::class,
            App\Service\ErrorMapper\ErrorMapperPlatesExtension::class,
            App\Service\TemplateConfigInjection\TemplateConfigInjectionPlatesExtension::class
        ],
    ],
];
