<?php

namespace Auth\Middleware;

use App\Exception\ForbiddenException;
use App\Exception\InvalidInputException;
use App\Exception\NotFoundException;
use Auth\Exception\UnauthorizedException;
use Auth\Service\Authentication as AuthenticationService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as MiddlewareInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Router\RouteResult;
use Laminas\Permissions\Rbac\Rbac;
use Exception;

/**
 * Class AuthorizationMiddleware
 * @package Auth\Middleware
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * AuthorizationMiddleware constructor
     *
     * @param Rbac $rbac
     * @param AuthenticationService $authenticationService
     */
    public function __construct(Rbac $rbac, AuthenticationService $authenticationService)
    {
        $this->rbac = $rbac;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $user = null;
        $role = 'guest';

        $token = $request->getHeaderLine('token');

        //  If a token is provided (in the header) then try to get the user it belongs to
        if (!empty($token)) {
            try {
                //  Use the token to prove the user is authenticated
                //  If the response is negative then an exception will be thrown to allow the front to sign out the session
                $user = $this->authenticationService->validateToken($token);

                if ($user instanceof User) {
                    $role = 'authenticated-user';
                }
            } catch (InvalidInputException $ignore) {
                //  The user could not be retrieved so throw the not authorized exception
                throw new UnauthorizedException('Not authorized');
            }
        }

        //  Determine the route was are attempting to access
        $route = $request->getAttribute(RouteResult::class);

        if (is_null($route)) {
            throw new NotFoundException('Page not found');
        }

        $routeName = $route->getMatchedRoute()->getName();

        //  Check that the role is allowed to access the requested route
        if ($this->rbac->hasRole($role) && $this->rbac->isGranted($role, $routeName)) {
            return $delegate->handle(
                $request->withAttribute('identity', $user)
            );
        }

        //  If we couldn't access the route then throw a forbidden exception
        throw new ForbiddenException('Access forbidden');
    }
}
