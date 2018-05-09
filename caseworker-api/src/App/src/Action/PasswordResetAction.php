<?php

namespace App\Action;

use App\Exception\InvalidInputException;
use App\Service\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class UserAction
 * @package App\Action
 */
class PasswordResetAction extends AbstractRestfulAction
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
     * MODIFY/PATCH modify action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     * @throws InvalidInputException
     */
    public function modifyAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestBody = $request->getParsedBody();

        //  Get the user by email address and update the token
        $user = $this->userService->getByEmail($requestBody['email']);

        $user = $this->userService->refreshToken($user->getId(), -1);

        //  Get the user details excluding the claims
        $userData = $user->getArrayCopy([
            'claims',
        ]);

        return new JsonResponse($userData);
    }
}
