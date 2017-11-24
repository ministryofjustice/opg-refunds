<?php

namespace App\Action\Claim;

use App\Form\AbstractForm;
use App\Form\Note;
use App\Form\PoaNoneFound;
use App\Form\ProcessNewClaim;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use RuntimeException;

/**
 * Class ClaimAction
 * @package App\Action\Claim
 */
class ClaimAction extends AbstractClaimAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        if ($claim === null) {
            throw new Exception('Claim not found', 404);
        }

        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render(
            'app::claim-page',
            $this->getViewModel($request, $claim, $form)
        ));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');
        $form = new ProcessNewClaim([
            'csrf' => $session['meta']['csrf'],
        ]);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $userId = $request->getAttribute('identity')->getId();
            $assignedClaimId = $this->claimService->assignNextClaim($userId);

            if ($assignedClaimId === 0) {
                //No available claims

                $this->setFlashInfoMessage($request, 'There are no more claims to process');

                return $this->redirectToRoute('home');
            }

            return $this->redirectToRoute('claim', ['id' => $assignedClaimId]);
        }

        // The only reason the form can be invalid is a CSRF check fail so no need to recover gracefully
        throw new Exception('CSRF failure', 500);
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //Even though we are adding a note here,
        //we are technically editing the claim by adding a note to it
        $claim = $this->getClaim($request);

        $form = $this->getForm($request, $claim);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $message = $form->get('message')->getValue();

                $note = $this->claimService->addNote($claim->getId(), NoteModel::TYPE_USER, $message);

                if ($note === null) {
                    throw new RuntimeException('Failed to add new note to claim with id: ' . $claim->getId());
                }

                return $this->redirectToRoute('claim', ['id' => $claim->getId()]);
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render(
            'app::claim-page',
            $this->getViewModel($request, $claim, $form)
        ));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');

        $form = new Note([
            'claim' => $claim,
            'csrf'  => $session['meta']['csrf'],
        ]);

        return $form;
    }

    /**
     * @param ServerRequestInterface $request
     * @param $claim
     * @param $form
     * @return array
     */
    private function getViewModel($request, $claim, $form): array
    {
        $session = $request->getAttribute('session');

        $poaNoneFoundForm = new PoaNoneFound([
            'csrf' => $session['meta']['csrf'],
        ]);

        return [
            'claim'            => $claim,
            'form'             => $form,
            'poaNoneFoundForm' => $poaNoneFoundForm,
        ];
    }
}
