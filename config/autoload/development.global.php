<?php

/**
 * Development-only configuration.
 *
 * Put settings you want enabled when under development mode in this file, and
 * check it into your repository.
 *
 * Developers on your team will then automatically enable them by calling on
 * `composer development-enable`.
 */

use Zend\Expressive\Container;
use Zend\Expressive\Middleware\ErrorResponseGenerator;

return (getenv('OPG_REFUNDS_STACK_TYPE') !== 'testing') ? [] :
[
    'dependencies' => [
        'invokables' => [
        ],
        'factories'  => [
            ErrorResponseGenerator::class       => Container\WhoopsErrorResponseGeneratorFactory::class,
            'Zend\Expressive\Whoops'            => Container\WhoopsFactory::class,
            'Zend\Expressive\WhoopsPageHandler' => Container\WhoopsPageHandlerFactory::class,
        ],
    ],

    'notify' => [
        'api' => [
            'key' => getenv('OPG_REFUNDS_NOTIFY_PHANTOM_API_KEY') ?: null,
        ],
    ],

    'whoops' => [
        'json_exceptions' => [
            'display'    => true,
            'show_trace' => true,
            'ajax_only'  => true,
        ],
    ],
];
