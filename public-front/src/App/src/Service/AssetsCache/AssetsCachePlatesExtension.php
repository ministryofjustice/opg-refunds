<?php
namespace App\Service\AssetsCache;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

/**
 * Preps the URL of a static asset for far future caching.
 *
 * Class AssetsCachePlatesExtension
 * @package App\Service\AssetsCache
 */
class AssetsCachePlatesExtension implements ExtensionInterface
{

    private $cacheToken;

    public function __construct(int $cacheToken)
    {
        $this->cacheToken = $cacheToken;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('static', [$this, 'amendUrl']);
    }

    public function amendUrl(string $path)
    {
        return str_replace( '/assets/', "/assets/{$this->cacheToken}/", $path );
    }

}
