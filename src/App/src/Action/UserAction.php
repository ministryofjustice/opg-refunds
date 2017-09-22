<?php

namespace App\Action;

use App\Service\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
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
}
