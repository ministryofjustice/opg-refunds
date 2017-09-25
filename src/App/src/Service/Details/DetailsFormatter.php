<?php

namespace App\Service\Details;

use Opg\Refunds\Caseworker\DataModel\Applications\Application as ApplicationModel;
use Opg\Refunds\Caseworker\DataModel\Common\Name as NameModel;

/**
 * Class DetailsFormatter
 * @package App\Service\Details
 */
class DetailsFormatter
{
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
}