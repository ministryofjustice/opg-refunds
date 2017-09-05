<?php

namespace Auth\Action;

use Auth\Service\AuthenticationService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Exception;

/**
 * Class AuthAction
 * @package Auth\Action
 */
class AuthAction implements ServerMiddlewareInterface
{
    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * AuthAction constructor
     *
     * @param AuthenticationService $authService
     */
    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestBody = $request->getParsedBody();

        $email = (isset($requestBody['email']) ? $requestBody['email'] : false);
        $password = (isset($requestBody['password']) ? $requestBody['password'] : false);

        if ($email && $password) {
            $result = $this->authService->validatePassword($email, $password);

            if (is_array($result)) {
                //  Remove the password value before returning the user details
                unset($result['password']);

                return new JsonResponse($result);
            }
        }

        throw new Exception('Not authorised', 401);
    }
}
