<?php
namespace App\Service\ErrorMapper;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

/**
 * Plates Extension providing a View Helper for the ErrorMapper service.
 *
 * Class ErrorMapperPlatesExtension
 * @package App\Service\ErrorMapper
 */
class ErrorMapperPlatesExtension implements ExtensionInterface
{
    private $mapper;

    public function __construct(ErrorMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('addLocalErrorMap', [$this->mapper, 'addLocalMap']);
        $engine->registerFunction('addFieldErrorMap', [$this->mapper, 'addFieldMap']);
        $engine->registerFunction('humanError', [$this->mapper, 'getHumanMessage']);
    }
}
