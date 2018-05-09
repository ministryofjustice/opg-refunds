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
            App\View\Date\DateFormatterPlatesExtension::class,
            App\View\Details\DetailsFormatterPlatesExtension::class,
            App\View\ErrorMapper\ErrorMapperPlatesExtension::class,
            App\View\Note\NoteFormatterPlatesExtension::class,
            App\View\Poa\PoaFormatterPlatesExtension::class,
            App\View\Search\SearchPlatesExtension::class,
            App\View\Url\UrlHelperPlatesExtension::class,
        ],
    ],
];
