<?php

namespace Auth\Middleware;

use Auth\Service\Authentication as AuthenticationService;
use Interop\Container\ContainerInterface;
use Zend\Permissions\Rbac\Rbac;
use Exception;

/**
 * Class AuthorizationMiddlewareFactory
 * @package Auth\Middleware
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

        return new AuthorizationMiddleware($rbac, $authenticationService);
    }
}
