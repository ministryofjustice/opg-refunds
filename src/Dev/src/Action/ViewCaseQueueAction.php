<?php
namespace Dev\Action;

use PDO;

use Zend\Crypt\PublicKey\Rsa;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ViewCaseQueueAction implements ServerMiddlewareInterface
{
    private $db;
    private $bankCipher;

    public function __construct(PDO $db, Rsa $bankCipher)
    {
        $this->db = $db;
        $this->bankCipher = $bankCipher;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $sql = 'SELECT * FROM refund.application ORDER BY created DESC LIMIT 1';

        $stmt = $this->db->query($sql);
        $row = $stmt->fetchObject();

        $payload = json_decode(stream_get_contents($row->data), true);

        $payload['account']['details'] = json_decode($this->bankCipher->decrypt($payload['account']['details']), true);

        return new JsonResponse(['payload' => $payload]);
    }

}
