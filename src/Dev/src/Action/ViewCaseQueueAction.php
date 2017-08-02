<?php
namespace Dev\Action;

use PDO;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ViewCaseQueueAction implements ServerMiddlewareInterface
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $sql = 'SELECT * FROM refund.application ORDER BY created DESC LIMIT 1';

        $stmt = $this->db->query($sql);
        $row = $stmt->fetchObject();

        $payload = json_decode(stream_get_contents($row->data), true);

        return new JsonResponse(['payload' => $payload]);
    }

}
