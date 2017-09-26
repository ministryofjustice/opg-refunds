<?php

namespace App\Action\User;

use App\Action\AbstractModelAction;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;

/**
 * Class AbstractUserAction
 * @package App\Action
 */
abstract class AbstractUserAction extends AbstractModelAction
{
    /**
     * Get the model concerned
     *
     * @return null|UserModel
     */
    protected function getUser()
    {
        $user = null;

        if (is_numeric($this->modelId)) {
            $userData = $this->getApiClient()->getUser($this->modelId);

            $user = new UserModel($userData);
        }

        return $user;
    }
}
