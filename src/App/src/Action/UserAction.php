<?php

namespace App\Action;

use App\Exception\InvalidInputException;
use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class UserAction
 * @package App\Action
 */
class UserAction extends AbstractRestfulAction
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * UserAction constructor
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService, ClaimService $claimService)
    {
        $this->userService = $userService;
        $this->claimService = $claimService;
    }

    /**
     * READ/GET index action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');
        $token = $request->getAttribute('token');

        if (is_numeric($userId)) {
            $user = $this->userService->getById($userId);

            return new JsonResponse($user->getArrayCopy());
        } elseif (!empty($token)) {
            $user = $this->userService->getByToken($token);

            return new JsonResponse($user->getArrayCopy());
        }

        //  Get all of the users
        $users = $this->userService->getAll();
        $usersData = [];

        foreach ($users as $user) {
            $usersData[] = $user->getArrayCopy([
                'token',
            ]);
        }

        return new JsonResponse($usersData);
    }

    /**
     * CREATE/POST add action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = new UserModel($request->getParsedBody());

        $user = $this->userService->add($user);

        return new JsonResponse($user->getArrayCopy());
    }

    /**
     * MODIFY/PATCH modify action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     * @throws InvalidInputException
     */
    public function modifyAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');
        $token = $request->getAttribute('token');

        $requestBody = $request->getParsedBody();

        if (!empty($token)) {
            //  If a token value has been provided then we are attempting to set the password for a pending user
            $user = $this->userService->getByToken($token);

            if ($user->getStatus() != UserModel::STATUS_PENDING) {
                throw new InvalidInputException('Password can not be set by token only');
            }

            $user = $this->userService->setPassword($user->getId(), $requestBody['password']);
        } else {
            //  Define the request field to update functions mappings
            $updateMappings = [
                'name'     => 'setName',
                'email'    => 'setEmail',
                'roles'    => 'setRoles',
                'status'   => 'setStatus',
                'password' => 'setPassword',
            ];

            foreach ($updateMappings as $fieldName => $updateFunction) {
                if (isset($requestBody[$fieldName]) && method_exists($this->userService, $updateFunction)) {
                    $this->userService->$updateFunction($userId, $requestBody[$fieldName]);
                }
            }

            $user = $this->userService->getById($userId);
        }

        return new JsonResponse($user->getArrayCopy());
    }

    /**
     * DELETE/DELETE delete action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     */
    public function deleteAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');

        $identity = $request->getAttribute('identity');

        //  Soft delete - set the status to deleted
        $userModel = $this->userService->setStatus($userId, UserModel::STATUS_DELETED);

        //  Loop through the claims assigned to the user and return all in progress ones to the pool
        foreach ($userModel->getClaims() as $claim) {
            /** @var ClaimModel $claim */
            if ($claim->getStatus() == ClaimModel::STATUS_IN_PROGRESS) {
                $this->claimService->removeClaim($claim->getId(), $identity->getId());
            }
        }

        return new JsonResponse($userModel->getArrayCopy());
    }
}
