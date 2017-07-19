<?php
namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class DonorDetailsAction implements
    ServerMiddlewareInterface,
    Initializers\UrlHelperInterface,
    Initializers\TemplatingSupportInterface
{
    use Initializers\UrlHelperTrait;
    use Initializers\TemplatingSupportTrait;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        $form = new Form\ActorDetails([
            'csrf' => $session['meta']['csrf']
        ]);

        $isUpdate = isset($session['donor']);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $session['donor'] = $form->getFormattedData();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session, $request->getAttribute('who')),
                        ['who'=>$request->getAttribute('who')]
                    )
                );
            }
        } elseif ($isUpdate) {
            // We are editing previously entered details.
            $form->setFormattedData($session['donor']);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::donor-details-page', [
            'form' => $form,
            'who' => $request->getAttribute('who')
        ]));
    }
}
