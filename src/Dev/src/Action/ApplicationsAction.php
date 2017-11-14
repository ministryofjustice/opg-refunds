<?php

namespace Dev\Action;

use Aws\Kms\KmsClient;
use App\Entity\Cases\Claim;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ApplicationsAction
 * @package Dev\Action
 */
class ApplicationsAction implements ServerMiddlewareInterface
{
    private $db;
    private $kmsClient;
    private $bankCipher;

    /**
     * @var EntityRepository
     */
    private $caseRepository;
    public function __construct(PDO $db, EntityManager $casesEntityManager, Rsa $bankCipher, KmsClient $kmsClient)
    {
        $this->db = $db;
        $this->kmsClient = $kmsClient;
        $this->bankCipher = $bankCipher;
        $this->caseRepository = $casesEntityManager->getRepository(Claim::class);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $sql = 'SELECT * FROM application ORDER BY created DESC';

        $applications = [];

        $rows = $this->db->query($sql);
        foreach ($rows as $row) {
            if ($row['data'] === null) {
                //Data has been ingested so get from claims table
                /** @var Claim $claim */
                $claim = $this->caseRepository->findOneBy(['id' => $row['id']]);
                $application = $claim->getJsonData();
            } else {
                $application = json_decode(gzinflate(stream_get_contents($row['data'])), true);
            }
            $application['account']['details'] = json_decode($this->decryptBankDetails($application['account']['details']), true);
            $application = array_merge(['id' => $row['id']], $application);
            $applications[] = $application;
        }

        return new JsonResponse($applications);
    }

    private function decryptBankDetails( $cipherText )
    {
        try {
            $clearText = $this->kmsClient->decrypt([
                'CiphertextBlob' => base64_decode($cipherText)
            ]);
            return $clearText->get('Plaintext');
        } catch ( \Exception $e ){
        }

        // Else fall back to old style encryption
        return $this->bankCipher->decrypt($cipherText);
    }
}
