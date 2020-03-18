<?php

use League\Plates\Engine as PlatesEngine;
use Mezzio\Plates\PlatesEngineFactory;
use Mezzio\Plates\PlatesRendererFactory;
use Mezzio\Template\TemplateRendererInterface;

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
