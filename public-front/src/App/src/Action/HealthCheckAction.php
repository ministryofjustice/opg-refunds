<?php
namespace App\Action;

use App;

use Throwable;

use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

use Interop\Container\ContainerInterface;

class HealthCheckAction implements RequestHandlerInterface
{

    private $container;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get('config');
    }

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {

        $result = [];

        //---

        $result['db'] = ['ok'=>$this->canAccessDatabase()];

        //---

        $result['sessions'] = ['ok'=>$this->canAccessSessions()];

        //---

        $result['processor'] = ['ok'=>$this->canCreateApplicationProcessor()];

        //---

        $ok = true;

        foreach ($result as $test) {
            $ok = $ok && $test['ok'];
        }

        $result['ok'] = $ok;

        //---

        $result['version'] = $this->config['version'];

        $result['stack'] = $this->config['stack'];

        //---

        return new JsonResponse($result, ($ok) ? 200 : 500);
    }

    private function canCreateApplicationProcessor()
    {
        try {
            $this->container->get(App\Service\Refund\ProcessApplication::class);

            return true;
        } catch (Throwable $e) {
        }

        return false;
    }


    private function canAccessDatabase()
    {
        try {
            // Creating this will throw an exception if there's an error connecting to the DB.
            $this->container->get(App\Service\Refund\Data\DataHandlerInterface::class);

            return true;
        } catch (Throwable $e) {
        }

        return false;
    }

    private function canAccessSessions()
    {
        try {
            $key = bin2hex(random_bytes(64));
            $data = ['test'=>true];

            $sessionManager = $this->container->get(App\Service\Session\SessionManager::class);

            $sessionManager->write($key, $data);

            $result = $sessionManager->read($key);

            $sessionManager->delete($key);

            if ($result === $data) {
                return true;
            }
        } catch (Throwable $e) {
        }

        return false;
    }
}
