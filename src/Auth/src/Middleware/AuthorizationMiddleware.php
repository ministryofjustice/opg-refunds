<?php

namespace Auth\Middleware;

use App\Exception\InvalidInputException;
use App\Service\User as UserService;
use Auth\Exception\UnauthorizedException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as MiddlewareInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Permissions\Rbac\Rbac;
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
     * @var UserService
     */
    private $userService;

    /**
     * AuthorizationMiddleware constructor
     *
     * @param Rbac $rbac
     * @param UserService $userService
     */
    public function __construct(Rbac $rbac, UserService $userService)
    {
        $this->rbac = $rbac;
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = null;
        $role = 'guest';

        $token = $request->getHeaderLine('token');

        //  If a token is provided then try to get the user it belongs to
        if (!empty($token)) {
            try {
                $user = $this->userService->getByToken($token);

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
            throw new Exception('Page not found', 404);
        }

        $routeName = $route->getMatchedRoute()->getName();

        //  Check that the role is allowed to access the requested route
        if ($this->rbac->hasRole($role) && $this->rbac->isGranted($role, $routeName)) {
            return $delegate->process(
                $request->withAttribute('identity', $user)
            );
        }

        //  If we couldn't access the route then throw a forbidden exception
        throw new Exception('Access forbidden', 403);
    }
}
