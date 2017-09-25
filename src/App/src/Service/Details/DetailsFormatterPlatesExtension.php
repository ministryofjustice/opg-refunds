<?php

namespace App\Service\Details;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

/**
 * Class DetailsFormatterPlatesExtension
 * @package App\Service\Details
 */
class DetailsFormatterPlatesExtension implements ExtensionInterface
{
    private $formatter;

    public function __construct(DetailsFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('getFormattedName', [$this->formatter, 'getFormattedName']);
        $engine->registerFunction('getApplicantName', [$this->formatter, 'getApplicantName']);
        $engine->registerFunction('getPaymentDetailsUsedText', [$this->formatter, 'getPaymentDetailsUsedText']);
        $engine->registerFunction('shouldShowPaymentDetailsUsedCountWarning', [$this->formatter, 'shouldShowPaymentDetailsUsedCountWarning']);
    }
}