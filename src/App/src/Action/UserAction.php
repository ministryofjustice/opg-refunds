<?php

namespace App\Action;

use App\Service\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class UserAction
 * @package App\Action
 */
class UserAction implements ServerMiddlewareInterface
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
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');

        if (is_numeric($userId)) {
            $user = $this->userService->getById($userId);

            return new JsonResponse($user->toArray());
        }

        //  Get all of the users
        $users = $this->userService->getAll();
        $usersData = [];

        foreach ($users as $user) {
            $usersData[] = $user->toArray();
        }

        return new JsonResponse($usersData);
    }
}
