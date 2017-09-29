<?php

namespace App\View\Details;

use Opg\Refunds\Caseworker\DataModel\Applications\Application as ApplicationModel;
use Opg\Refunds\Caseworker\DataModel\Common\Name as NameModel;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use InvalidArgumentException;

/**
 * Class DetailsFormatterPlatesExtension
 * @package App\View\Details
 */
class DetailsFormatterPlatesExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('getFormattedName', [$this, 'getFormattedName']);
        $engine->registerFunction('getApplicantName', [$this, 'getApplicantName']);
        $engine->registerFunction('getPaymentDetailsUsedText', [$this, 'getPaymentDetailsUsedText']);
        $engine->registerFunction('shouldShowPaymentDetailsUsedCountWarning', [$this, 'shouldShowPaymentDetailsUsedCountWarning']);
    }

    public function getFormattedName(NameModel $name)
    {
        return "{$name->getTitle()} {$name->getFirst()} {$name->getLast()}";
    }

    public function getApplicantName(ApplicationModel $application)
    {
        if ($application->getApplicant() === 'donor') {
            return "{$this->getFormattedName($application->getDonor()->getName())} (Donor)";
        } elseif ($application->getApplicant() === 'attorney') {
            return "{$this->getFormattedName($application->getAttorney()->getName())} (Attorney)";
        }

        return '';
    }

    public function getPaymentDetailsUsedText(int $accountHashCount)
    {
        if ($accountHashCount === null) {
            throw new InvalidArgumentException('Account hash count must be set');
        }

        if ($accountHashCount < 1) {
            throw new InvalidArgumentException('Account hash count is set to an invalid value: ' . $accountHashCount);
        }

        if ($accountHashCount === 1) {
            return "Payment details used once";
        }

        if ($accountHashCount === 2) {
            return "Payment details used twice";
        }

        if ($accountHashCount === 2) {
            return "Payment details used {$accountHashCount} times";
        }
    }

    public function shouldShowPaymentDetailsUsedCountWarning(int $accountHashCount)
    {
        return $accountHashCount > 2;
    }
}