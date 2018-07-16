<?php
namespace App\Action;

use App;

use Throwable;

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
        $this->container = $container;
        $this->config = $container->get('config');
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $result = [];

        //---

        $result['keys'] = [
            'symmetric' => $this->canSessionKeysAreValid(),
        ];

        $result['keys']['ok'] = $result['keys']['symmetric']['ok'];

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

    private function canSessionKeysAreValid()
    {
        $result = [
            'number' => 0,
            'ok' => false
        ];

        $config = $this->config['session'];

        if (!isset($config['encryption']['keys'])) {
            return $result;
        }

        $keys = explode(',', $config['encryption']['keys']);

        $result['number'] = count($keys);

        try {
            foreach ($keys as $key) {
                $items = explode(':', $key);
                hex2bin($items[1]);
            }

            $result['ok'] = true;

            return $result;
        } catch (Throwable $e) {
        }

        return $result;
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
