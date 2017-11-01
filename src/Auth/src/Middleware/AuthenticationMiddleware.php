<?php

namespace Auth\Middleware;

use App\Exception\InvalidInputException;
use Auth\Exception\UnauthorizedException;
use Auth\Service\Authentication;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthenticationMiddleware
 * @package Auth\Middleware
 */
class AuthenticationMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var Authentication
     */
    private $authService;

    /**
     * AuthenticationMiddleware constructor
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
     * @return \Psr\Http\Message\ResponseInterface
     * @throws UnauthorizedException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $token = $request->getHeaderLine('token');

        try {
            $user = $this->authService->validateToken($token);
        } catch (InvalidInputException $ignore) {
            //  If the token validation failed then throw as not authorised
            throw new UnauthorizedException('Not authorized');
        }

        return $delegate->process($request);
    }
}
