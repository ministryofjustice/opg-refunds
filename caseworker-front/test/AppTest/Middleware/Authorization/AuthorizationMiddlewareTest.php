<?php

namespace AppTest\Service\Refund\Middleware\Authorization;

use App\Middleware\Authorization\AuthorizationMiddleware;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Expressive\Handler\NotFoundHandler;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\RouteResult;
use Zend\Permissions\Rbac\Rbac;

class AuthorizationMiddlewareTest extends MockeryTestCase
{
    /**
     * @var AuthenticationService|MockInterface
     */
    private $authService;

    /**
     * @var UrlHelper|MockInterface
     */
    private $urlHelper;

    /**
     * @var Rbac|MockInterface
     */
    private $rbac;

    /**
     * @var NotFoundHandler|MockInterface
     */
    private $notFoundHandler;

    /**
     * @var AuthorizationMiddleware
     */
    private $authorizationMiddleware;

    public function setUp() : void
    {
        $this->authService = Mockery::mock(AuthenticationService::class);

        $this->urlHelper = Mockery::mock(UrlHelper::class);

        $this->rbac = Mockery::mock(Rbac::class);

        $this->notFoundHandler = Mockery::mock(NotFoundHandler::class);

        $this->authorizationMiddleware = new AuthorizationMiddleware($this->authService, $this->urlHelper, $this->rbac, $this->notFoundHandler);
    }

    public function testProcessRouteMatchFailureNullResponse() : void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRoles')->withNoArgs()->andReturn([])->once();

        $this->authService->shouldReceive('getIdentity')->andReturn($user)->once();
        $this->notFoundHandler->shouldReceive('handle')->once();

        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')->andReturn(null)->once();

        $delegate = Mockery::mock(RequestHandlerInterface::class);

        $this->authorizationMiddleware->process($request, $delegate);
    }

    public function testProcessRouteMatchFailureFailedRouteResult() : void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRoles')->withNoArgs()->andReturn([])->once();

        $this->authService->shouldReceive('getIdentity')->andReturn($user)->once();
        $this->notFoundHandler->shouldReceive('handle')->once();

        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')->andReturn(RouteResult::fromRouteFailure(null))->once();

        $delegate = Mockery::mock(RequestHandlerInterface::class);

        $this->authorizationMiddleware->process($request, $delegate);
    }
}
