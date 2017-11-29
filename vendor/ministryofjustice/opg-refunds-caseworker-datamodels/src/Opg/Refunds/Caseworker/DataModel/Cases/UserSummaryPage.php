<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractPage;

/**
 * Class UserSummaryPage
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class UserSummaryPage extends AbstractPage
{
    /**
     * @var UserSummary[]
     */
    protected $userSummaries;

    /**
     * @return UserSummary[]
     */
    public function getUserSummaries()
    {
        return $this->userSummaries;
    }

    /**
     * @param User[] $userSummaries
     * @return $this
     */
    public function setUserSummaries(array $userSummaries): UserSummaryPage
    {
        $this->userSummaries = $userSummaries;

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
            case 'userSummaries':
                return array_map(function ($value) {
                    return ($value instanceof UserSummary ? $value : new UserSummary($value));
                }, $value);
            default:
                return parent::map($property, $value);
        }
    }
}