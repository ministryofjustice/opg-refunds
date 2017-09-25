<?php

namespace App\Action\Poa;


use App\Form\AbstractForm;
use App\Form\PoaMeris;
use App\Service\Claim as ClaimService;
use Psr\Http\Message\ServerRequestInterface;

class PoaMerisAction extends AbstractPoaAction
{
    public function __construct(ClaimService $claimService)
    {
        $this->templateName = 'app::poa-meris-page';

        parent::__construct($claimService);
    }

    /**
     * @param ServerRequestInterface $request
     * @return AbstractForm
     */
    public function getForm(ServerRequestInterface $request): AbstractForm
    {
        $session = $request->getAttribute('session');
        $form = new PoaMeris([
            'csrf' => $session['meta']['csrf']
        ]);
        return $form;
    }
}