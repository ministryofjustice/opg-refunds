<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractPage;

class ClaimPage extends AbstractPage
{
    /**
     * @var Claim[]
     */
    protected $claims;

    /**
     * @return Claim[]
     */
    public function getClaims()
    {
        return $this->claims;
    }

    /**
     * @param Claim[] $claims
     * @return $this
     */
    public function setClaims(array $claims): ClaimPage
    {
        $this->claims = $claims;

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
            case 'claims':
                return array_map(function ($value) {
                    return ($value instanceof Claim ? $value : new Claim($value));
                }, $value);
            default:
                return parent::map($property, $value);
        }
    }
}