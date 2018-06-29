<?php

namespace App\Action\Poa;

use App\Form\AbstractForm;
use App\Form\PoaNoneFound;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Psr\Http\Message\ServerRequestInterface;
use Exception;

/**
 * Class PoaNoneFoundAction
 * @package App\Action\Poa
 */
class PoaNoneFoundAction extends AbstractPoaAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $system = $request->getAttribute('system');

            switch ($system) {
                case PoaModel::SYSTEM_SIRIUS:
                    $this->claimService->setNoSiriusPoas($this->modelId, !$claim->isNoSiriusPoas());
                    break;
                case PoaModel::SYSTEM_MERIS:
                    $this->claimService->setNoMerisPoas($this->modelId, !$claim->isNoMerisPoas());
                    break;
            }

            return $this->redirectToRoute('claim', ['id' => $this->modelId]);
        }

        // The only reason the form can be invalid is a CSRF check fail so no need to recover gracefully
        throw new Exception('CSRF failure', 500);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');

        $form = new PoaNoneFound([
            'csrf' => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
