<?php
namespace App\Service\TemplateConfigInjection;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

/**
 * Provides access to a subsection of the config array from within layouts
 *
 * Accessible from a template via $this->config()
 *
 * Class TemplateConfigInjectionPlatesExtension
 * @package App\Service\TemplateConfigInjection
 */
class TemplateConfigInjectionPlatesExtension implements ExtensionInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function register(Engine $engine)
    {
        $config = $this->config;

        $engine->registerFunction('config', function () use ($config) {
            return $config;
        });
    }
}
