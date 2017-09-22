<?php
namespace App\Service\ErrorMapper;

use Zend\Stdlib\ArrayUtils;

class ErrorMapper
{
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
