<?php
namespace App\Action;

use Exception;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

use Interop\Container\ContainerInterface;

class HealthCheckAction implements ServerMiddlewareInterface
{

    private $container;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        //$this->container = $container;
        $this->config = $container->get('config');
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $result = [];

        //---

        $keysFound = $this->numberOdValidAsymmetricKeys();

        $result['keys'] = [
            'asymmetric' => [
                'number' => $keysFound,
                'ok' => ($keysFound === 2)
            ],
            'ok' => ($keysFound === 2)
        ];

        //---

        $ok = true;

        foreach ($result as $test) {
            $ok = $ok && $test['ok'];
        }

        $result['ok'] = $ok;

        //---

        return new JsonResponse($result);
    }


    private function numberOdValidAsymmetricKeys()
    {

        if (!isset($this->config['security']['rsa']['keys']['public'])) {
            return 0;
        }

        $paths = $this->config['security']['rsa']['keys']['public'];

        if (!is_array($paths)) {
            return 0;
        }

        //---

        $found = 0;
        foreach ($paths as $path) {
            try {
                // This will exception if the file can't be found or if it's not valid as a key
                \Zend\Crypt\PublicKey\Rsa\PublicKey::fromFile($path);
                $found++;
            } catch (Exception $e) {
            }
        }

        return $found;
    }
}
