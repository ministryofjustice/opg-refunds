<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractPage;

/**
 * Class ClaimSummaryPage
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class ClaimSummaryPage extends AbstractPage
{
    /**
     * @var ClaimSummary[]
     */
    protected $claimSummaries;

    /**
     * @return ClaimSummary[]
     */
    public function getClaimSummaries()
    {
        return $this->claimSummaries;
    }

    /**
     * @param Claim[] $claimSummaries
     * @return $this
     */
    public function setClaimSummaries(array $claimSummaries): ClaimSummaryPage
    {
        $this->claimSummaries = $claimSummaries;

        return $this;
    }

    /**
     * Map properties to correct types
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    protected function map($property, $value)
    {
        switch ($property) {
            case 'claimSummaries':
                return array_map(function ($value) {
                    return ($value instanceof ClaimSummary ? $value : new ClaimSummary($value));
                }, $value);
            default:
                return parent::map($property, $value);
        }
    }
}