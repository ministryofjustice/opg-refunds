<?php

namespace App\Action;

use App\Exception\InvalidInputException;
use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

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
     * @return JsonResponse
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $userId = $request->getAttribute('id');
        $token = $request->getAttribute('token');

        if (is_numeric($userId)) {
            $user = $this->userService->getById($userId);

            return new JsonResponse($user->getArrayCopy());
        } elseif (!empty($token)) {
            $user = $this->userService->getByToken($token, true);

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
     * @return JsonResponse
     */
    public function addAction(ServerRequestInterface $request)
    {
        $user = new UserModel($request->getParsedBody());

        $user = $this->userService->add($user);

        return new JsonResponse($user->getArrayCopy());
    }

    /**
     * UPDATE/PUT edit action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function editAction(ServerRequestInterface $request)
    {
        //  Get the user ID and refresh the set up token value
        $userId = $request->getAttribute('id');

        $user = $this->userService->refreshToken($userId, -1);

        return new JsonResponse($user->getArrayCopy());
    }

    /**
     * MODIFY/PATCH modify action
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     * @throws InvalidInputException
     */
    public function modifyAction(ServerRequestInterface $request)
    {
        $userId = $request->getAttribute('id');
        $token = $request->getAttribute('token');

        $requestBody = $request->getParsedBody();

        if (!empty($token)) {
            //  If a token value has been provided in the request then we are attempting to set the password for a user
            //  This can only be done if the token expires value in the database has been set to -1 also
            $user = $this->userService->getByToken($token, true);

            $user = $this->userService->setPassword($user->getId(), $requestBody['password']);

            //  Refresh the token to remove the old copy value
            $this->userService->refreshToken($user->getId(), -1);
        } else {
            //  Define the request field to update functions mappings
            $updateMappings = [
                'email'    => 'setEmail',
                'name'     => 'setName',
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
     * @return JsonResponse
     */
    public function deleteAction(ServerRequestInterface $request)
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
