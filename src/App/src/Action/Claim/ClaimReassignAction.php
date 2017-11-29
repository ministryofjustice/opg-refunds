<?php

namespace App\Action\Claim;

use App\Form\AbstractForm;
use App\Form\ClaimReassign;
use App\Service\Claim\Claim as ClaimService;
use App\Service\User\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Opg\Refunds\Caseworker\DataModel\Cases\UserSummary as UserSummaryModel;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

class ClaimReassignAction extends AbstractClaimAction
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(ClaimService $claimService, UserService $userService)
    {
        parent::__construct($claimService);
        $this->userService = $userService;
    }

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

        /** @var ClaimReassign $form */
        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-reassign-page', [
            'form'  => $form,
            'claim' => $claim
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        /** @var ClaimReassign $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $formData = $form->getData();

            $userId = (int)$formData['user-id'];
            $reason = $formData['reason'];

            $assignedClaim = $this->claimService->assignClaim($claim->getId(), $userId, $reason);
            $assignedClaimId = $assignedClaim['assignedClaimId'];
            $assignedToName = $assignedClaim['assignedToName'];

            if ($assignedClaimId === 0) {
                throw new RuntimeException('Failed to reassign claim with id: ' . $this->modelId);
            }

            $this->setFlashInfoMessage($request, 'Claim reassigned to ' . $assignedToName);

            return $this->redirectToRoute('claim', ['id' => $assignedClaimId]);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-reassign-page', [
            'form'  => $form,
            'claim' => $claim
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');

        $userSummaryPage = $this->userService->searchUsers(null, null, null, UserModel::STATUS_ACTIVE);
        $userSummaries = $userSummaryPage->getUserSummaries();

        $form = new ClaimReassign([
            'claim'  => $claim,
            'userSummaries' => $userSummaries,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}