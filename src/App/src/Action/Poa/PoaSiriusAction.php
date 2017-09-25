<?php

namespace App\Action\Poa;

use App\Form\AbstractForm;
use App\Form\PoaSirius;
use App\Service\Claim as ClaimService;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;

class PoaSiriusAction extends AbstractPoaAction
{
    public function __construct(ClaimService $claimService)
    {
        $this->templateName = 'app::poa-sirius-page';

        parent::__construct($claimService);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    public function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');
        $form = new PoaSirius([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);
        return $form;
    }
}