<?php

namespace Auth\Action;

use App\Exception\InvalidInputException;
use Auth\Exception\UnauthorizedException;
use Auth\Service\Authentication;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class AuthAction
 * @package Auth\Action
 */
class AuthAction implements ServerMiddlewareInterface
{
    /**
     * @var Authentication
     */
    private $authService;

    /**
     * AuthAction constructor
     *
     * @param Authentication $authService
     */
    public function __construct(Authentication $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     * @throws UnauthorizedException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestBody = $request->getParsedBody();

        if (isset($requestBody['email']) && isset($requestBody['password'])) {
            try {
                $user = $this->authService->validatePassword($requestBody['email'], $requestBody['password']);

                //  Get the user details excluding the cases
                $userData = $user->toArray([
                    'claim',
                ]);

                return new JsonResponse($userData);
            } catch (InvalidInputException $ignore) {
                //  Authentication failed - exception thrown below
            }
        }

        throw new UnauthorizedException('Not authorized');
    }
}
