<?php

namespace Dev\Action;

use App\Crypt\Hybrid as HybridCipher;
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
    private $bankCipher;
    private $fullCipher;

    public function __construct(PDO $db, Rsa $bankCipher, HybridCipher $fullCipher)
    {
        $this->db = $db;
        $this->bankCipher = $bankCipher;
        $this->fullCipher = $fullCipher;
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
            $application = json_decode(gzinflate($this->fullCipher->decrypt(stream_get_contents($row['data']))), true);
            $application['account']['details'] = json_decode($this->bankCipher->decrypt($application['account']['details']), true);
            $application = array_merge(['id' => $row['id']], $application);
            $applications[] = $application;
        }

        return new JsonResponse($applications);
    }
}
