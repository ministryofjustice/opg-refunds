<?php

use Opg\Refunds\Log;

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => '/tmp/config-cache-opg-refunds-caseworker-front.php',
];

$aggregator = new ConfigAggregator([
    Mezzio\ConfigProvider::class,
    Mezzio\Router\ConfigProvider::class,
    \Laminas\Log\ConfigProvider::class,
    // Include cache configuration
    new ArrayProvider($cacheConfig),

    // Modules config
    Api\ConfigProvider::class,
    App\ConfigProvider::class,
    Log\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
    // Load development config if it exists
    new PhpFileProvider('config/debug.config.php'),
    new PhpFileProvider('config/development.config.php'),
], $cacheConfig['config_cache_path'], [\Laminas\ZendFrameworkBridge\ConfigPostProcessor::class]);

return $aggregator->getMergedConfig();
