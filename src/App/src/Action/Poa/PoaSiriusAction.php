<?php

namespace App\Action\Poa;

use App\Action\AbstractClaimAction;
use App\Form\AbstractForm;
use App\Form\PoaSirius;
use App\Service\Claim as ClaimService;
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
     * @return AbstractForm
     */
    public function getForm(ServerRequestInterface $request): AbstractForm
    {
        $session = $request->getAttribute('session');
        $form = new PoaSirius([
            'csrf' => $session['meta']['csrf']
        ]);
        return $form;
    }
}