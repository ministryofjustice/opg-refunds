<?php

namespace Auth\Middleware;

use Auth\Service\AuthenticationService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;

/**
 * Class AuthMiddleware
 * @package Auth\Middleware
 */
class AuthMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * AuthMiddleware constructor
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
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $token = $request->getHeaderLine('token');

        if (is_string($token) && $this->authService->validateToken($token)) {
            return $delegate->process($request);
        }

        throw new Exception('Unauthorised access');
    }
}
