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

        $keysFound = $this->numberOfValidAsymmetricKeys();

        $result['keys'] = [
            'symmetric' => $this->canSessionKeysAreValid(),
            'asymmetric' => [
                'number' => $keysFound,
                'ok' => ($keysFound === 2)
            ],
        ];

        $result['keys']['ok'] = $result['keys']['symmetric']['ok'] && $result['keys']['asymmetric']['ok'];

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

        return new JsonResponse($result, ($ok) ? 200 : 500 );
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

    private function numberOfValidAsymmetricKeys()
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
            } catch (Throwable $e) {
            }
        }

        return $found;
    }
}
