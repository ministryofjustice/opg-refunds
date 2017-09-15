<?php
namespace Dev\Action;

use Applications\Service\DataMigration;
use Zend\Crypt\PublicKey\Rsa;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ViewCaseQueueAction implements ServerMiddlewareInterface
{
    /**
     * @var DataMigration
     */
    private $dataMigrationService;
    /**
     * @var Rsa
     */
    private $bankCipher;

    public function __construct(DataMigration $dataMigrationService, Rsa $bankCipher)
    {
        $this->bankCipher = $bankCipher;
        $this->dataMigrationService = $dataMigrationService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $application = $this->dataMigrationService->getNextApplication();
        if ($application === null) {
            return new JsonResponse([]);
        }

        $decryptedData = $this->dataMigrationService->getDecryptedData($application);

        $payload = json_decode($decryptedData, true);

        $payload['account']['details'] = json_decode($this->bankCipher->decrypt($payload['account']['details']), true);

        return new JsonResponse(['id' => $application->getId(), 'payload' => $payload]);
    }
}
