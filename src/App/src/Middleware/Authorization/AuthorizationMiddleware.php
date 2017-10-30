<?php

namespace App\Middleware\Authorization;

use Api\Exception\ApiException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as MiddlewareInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\RouteResult;
use Zend\Permissions\Rbac\Rbac;
use Exception;

/**
 * Class AuthorizationMiddleware
 * @package App\Middleware\Auth
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * AuthorizationMiddleware constructor
     *
     * @param AuthenticationService $authService
     * @param UrlHelper $urlHelper
     * @param Rbac $rbac
     */
    public function __construct(AuthenticationService $authService, UrlHelper $urlHelper, Rbac $rbac)
    {
        $this->authService = $authService;
        $this->urlHelper = $urlHelper;
        $this->rbac = $rbac;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $identity = $this->authService->getIdentity();

        //  Determine the roles of the user - if not logged in then they are a guest
        $roles = ($identity instanceof User ? $identity->getRoles() : ['guest']);

        //  Determine the route was are attempting to access
        $route = $request->getAttribute(RouteResult::class);

        //  TODO - Move this to a proper 404 handler
        if (is_null($route)) {
            throw new Exception('Page not found', 404);
        }

        $routeName = $route->getMatchedRoute()->getName();

        //  Check each role to see if the user has access to the route
        foreach ($roles as $role) {
            if ($this->rbac->hasRole($role) && $this->rbac->isGranted($role, $routeName)) {
                //  Catch any unauthorized exceptions and trigger a sign out if required
                try {
                    return $delegate->process(
                        $request->withAttribute('identity', $identity)
                    );
                } catch (ApiException $ae) {
                    if ($ae->getCode() === 401) {
                        return new RedirectResponse($this->urlHelper->generate('sign.out'));
                    } else {
                        throw $ae;
                    }
                }
            }
        }

        //  If there is no identity (not logged in) then redirect to the sign in screen
        if (is_null($identity)) {
            return new RedirectResponse($this->urlHelper->generate('sign.in'));
        }

        //  Throw a forbidden exception
        throw new Exception('Access forbidden', 403);
    }
}
