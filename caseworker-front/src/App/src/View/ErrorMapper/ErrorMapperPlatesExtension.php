<?php

namespace App\View\ErrorMapper;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Laminas\Stdlib\ArrayUtils;

/**
 * Plates Extension providing a View Helper for the ErrorMapper service.
 *
 * Class ErrorMapperPlatesExtension
 * @package App\View\ErrorMapper
 */
class ErrorMapperPlatesExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('addErrorMap', [$this, 'addErrorMap']);
        $engine->registerFunction('summaryError', [$this, 'getSummaryError']);
        $engine->registerFunction('fieldError', [$this, 'getFieldError']);
    }
    /**
     * Store of error messages.
     * @var array
     */
    private $errors = [];

    public function addErrorMap(array $map, $locale = 'en-GB')
    {
        // Ensure there's an array for the locale
        if (!isset($this->errors[$locale])) {
            $this->errors[$locale] = [];
        }

        $this->errors[$locale] = ArrayUtils::merge($this->errors[$locale], $map);
    }

    public function getSummaryError($field, $slug, $locale = 'en-GB')
    {
        $slug = explode(':', $slug)[0];
        return ($this->errors[$locale][$field][$slug]['summary']) ?? $slug;
    }

    public function getFieldError($field, $slug, $locale = 'en-GB')
    {
        $slug = explode(':', $slug)[0];
        return ($this->errors[$locale][$field][$slug]['field']) ?? $slug;
    }
}
