<?php

namespace App\Service\User;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;

class User implements ApiClientInterface
{
    use ApiClientTrait;

    /**
     * Get user details
     *
     * @param int $userId
     * @return UserModel
     */
    public function getUser(int $userId)
    {
        $userData = $this->getApiClient()->httpGet('/v1/cases/user/' . $userId);

        if (empty($userData)) {
            return null;
        }

        return new UserModel($userData);
    }

    /**
     * Get all users
     *
     * @return array
     */
    public function getUsers()
    {
        //  Get all users
        $users = [];

        //  Even though the user details are in the session get them again with a GET call to the API
        $usersData = $this->getApiClient()->httpGet('/v1/cases/user');

        if (empty($usersData)) {
            return null;
        }

        foreach ($usersData as $userData) {
            $users[] = new UserModel($userData);
        }

        return $users;
    }
}