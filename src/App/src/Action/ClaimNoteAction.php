<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ClaimNoteAction
 * @package App\Action
 */
class ClaimNoteAction extends AbstractRestfulAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * READ/GET index action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claimId = $request->getAttribute('claimId');

        $identity = $request->getAttribute('identity');

        $claim = $this->claimService->get($claimId, $identity->getId());

        $noteId = $request->getAttribute('id');
        if ($noteId === null) {
            //  Return all of the notes
            $notesData = [];

            foreach ($claim->getNotes() as $note) {
                $notesData[] = $note->getArrayCopy();
            }

            return new JsonResponse($notesData);
        } else {
            //  Return a specific note
            foreach ($claim->getNotes() as $note) {
                if ($note->getId() === $noteId) {
                    return new JsonResponse($note->getArrayCopy());
                }
            }

            return new JsonResponse([]);
        }
    }

    /**
     * CREATE/POST add action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestBody = $request->getParsedBody();
        $note = new NoteModel($requestBody);

        $claimId = $request->getAttribute('claimId');

        $identity = $request->getAttribute('identity');

        $note = $this->claimService->addNote($claimId, $identity->getId(), $note->getTitle(), $note->getMessage());

        return new JsonResponse($note->getArrayCopy());
    }
}