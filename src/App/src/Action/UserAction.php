<?php

namespace App\Action;

use App\Service\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
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
     * UserAction constructor
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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

        if (is_numeric($userId)) {
            $user = $this->userService->getById($userId);

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
     */
    public function modifyAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');

        $requestBody = $request->getParsedBody();

        //  Define the request field to update functions mappings
        $updateMappings = [
            'name'   => 'setName',
            'email'  => 'setEmail',
            'roles'  => 'setRoles',
            'status' => 'setStatus',
        ];

        foreach ($updateMappings as $fieldName => $updateFunction) {
            if (isset($requestBody[$fieldName]) && method_exists($this->userService, $updateFunction)) {
                $this->userService->$updateFunction($userId, $requestBody[$fieldName]);
            }
        }

        $user = $this->userService->getById($userId);

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

        //  Soft delete - set the status to deleted
        $userModel = $this->userService->setStatus($userId, UserModel::STATUS_DELETED);

        return new JsonResponse($userModel->getArrayCopy());
    }
}
