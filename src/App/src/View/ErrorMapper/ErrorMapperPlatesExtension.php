<?php

namespace App\View\ErrorMapper;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

/**
 * Plates Extension providing a View Helper for the ErrorMapper service.
 *
 * Class ErrorMapperPlatesExtension
 * @package App\View\ErrorMapper
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
        $engine->registerFunction('addErrorMap', [$this->mapper, 'addErrorMap']);
        $engine->registerFunction('summaryError', [$this->mapper, 'getSummaryError']);
        $engine->registerFunction('fieldError', [$this->mapper, 'getFieldError']);
    }
}
