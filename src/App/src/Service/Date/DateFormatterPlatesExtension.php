<?php

namespace App\Service\Date;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

/**
 * Class DateFormatterPlatesExtension
 * @package App\Service\Date
 */
class DateFormatterPlatesExtension implements ExtensionInterface
{
    private $formatter;

    public function __construct(DateFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('getDayAndFullTextMonth', [$this->formatter, 'getDayAndFullTextMonth']);
        $engine->registerFunction('getDaysAgo', [$this->formatter, 'getDaysAgo']);
        $engine->registerFunction('getLogTimestamp', [$this->formatter, 'getLogTimestamp']);
    }
}
