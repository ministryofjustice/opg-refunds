<?php
namespace Dev\Action;

use Ingestion\Service\ApplicationIngestion;
use Zend\Crypt\PublicKey\Rsa;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ViewClaimQueueAction implements ServerMiddlewareInterface
{
    /**
     * @var ApplicationIngestion
     */
    private $dataMigrationService;
    /**
     * @var Rsa
     */
    private $bankCipher;

    public function __construct(ApplicationIngestion $dataMigrationService, Rsa $bankCipher)
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
