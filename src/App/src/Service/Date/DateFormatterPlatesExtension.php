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
        $engine->registerFunction('getTimeIntervalAgo', [$this->formatter, 'getTimeIntervalAgo']);
        $engine->registerFunction('getLogDateString', [$this->formatter, 'getLogDateString']);
        $engine->registerFunction('getLogTimeString', [$this->formatter, 'getLogTimeString']);
        $engine->registerFunction('getDateOfBirthString', [$this->formatter, 'getDateOfBirthString']);
        $engine->registerFunction('getReceivedDateString', [$this->formatter, 'getReceivedDateString']);
    }
}
