<?php

namespace App\Middleware\Authorization;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Permissions\Rbac\Rbac;
use Exception;
use Zend\Expressive\Delegate\NotFoundDelegate;


/**
 * Class AuthorizationMiddlewareFactory
 * @package App\Middleware\Auth
 */
class AuthorizationMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthorizationMiddleware
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['rbac']['roles'])) {
            throw new Exception('Rbac roles are not configured');
        }

        if (!isset($config['rbac']['permissions'])) {
            throw new Exception('Rbac permissions are not configured');
        }

        $rbac = new Rbac();
        $rbac->setCreateMissingRoles(true);

        // roles and parents
        foreach ($config['rbac']['roles'] as $role => $parents) {
            $rbac->addRole($role, $parents);
        }

        // permissions
        foreach ($config['rbac']['permissions'] as $role => $permissions) {
            foreach ($permissions as $perm) {
                $rbac->getRole($role)->addPermission($perm);
            }
        }

        //  Pass any extra services into the authorization middleware
        $authenticationService = $container->get(AuthenticationService::class);
        $urlHelper = $container->get(UrlHelper::class);

        return new AuthorizationMiddleware(
            $authenticationService,
            $urlHelper,
            $rbac,
            $container->get(NotFoundDelegate::class)
        );
    }
}
