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
        $userData = $this->getApiClient()->httpGet('/v1/user/' . $userId);

        return $this->createDataModel($userData);
    }

    /**
     * Get all users
     *
     * @return array
     */
    public function getUsers()
    {
        $usersData = $this->getApiClient()->httpGet('/v1/user');

        return $this->createModelCollection($usersData);
    }

    /**
     * Create new user
     *
     * @param UserModel $user
     * @return null|UserModel
     */
    public function createUser(UserModel $user)
    {
        $userData = $this->getApiClient()->httpPost('/v1/user', $user->getArrayCopy());

        return $this->createDataModel($userData);
    }

    /**
     * Update existing user
     *
     * @param int $userId
     * @param array $data
     * @return null|UserModel
     */
    public function updateUser(int $userId, array $data)
    {
        $userData = $this->getApiClient()->httpPatch('/v1/user/' . $userId, $data);

        return $this->createDataModel($userData);
    }

    /**
     * Delete user
     *
     * @param int $userId
     * @return null|UserModel
     */
    public function deleteUser(int $userId)
    {
        $userData = $this->getApiClient()->httpDelete('/v1/user/' . $userId);

        return $this->createDataModel($userData);
    }

    /**
     * Get user details by token
     *
     * @param string $token
     * @return UserModel
     */
    public function getUserByToken(string $token)
    {
        $userData = $this->getApiClient()->httpGet('/v1/user-by-token/' . $token);

        return $this->createDataModel($userData);
    }

    /**
     * Update password using a token value - for pending users only
     *
     * @param $userId
     * @param $password
     * @return null|UserModel
     */
    public function updatePassword($userId, $password)
    {
        $userData = $this->getApiClient()->httpPatch('/v1/user/' . $userId, [
            'password' => $password,
        ]);

        return $this->createDataModel($userData);
    }

    /**
     * Update password using a token value
     *
     * @param $token
     * @param $password
     * @return null|UserModel
     */
    public function updatePasswordByToken($token, $password)
    {
        $userData = $this->getApiClient()->httpPatch('/v1/user-by-token/' . $token, [
            'password' => $password,
        ]);

        return $this->createDataModel($userData);
    }

    /**
     * Reset a password for a user - this will generate a token against the user that can then be used for this purpose
     *
     * @param $email
     * @return null|UserModel
     */
    public function resetPassword($email)
    {
        $userData = $this->getApiClient()->httpPatch('/v1/reset-password', [
            'email' => $email,
        ]);

        return $this->createDataModel($userData);
    }

    /**
     * Create model from array data
     *
     * @param array|null $data
     * @return null|UserModel
     */
    private function createDataModel(array $data = null)
    {
        if (is_array($data) && !empty($data)) {
            return new UserModel($data);
        }

        return null;
    }

    /**
     * Create a collection (array) of models
     *
     * @param array|null $data
     * @return array
     */
    private function createModelCollection(array $data = null)
    {
        $models = [];

        if (is_array($data)) {
            foreach ($data as $dataItem) {
                $models[] = $this->createDataModel($dataItem);
            }
        };

        return $models;
    }
}