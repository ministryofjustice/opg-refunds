<?php
namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;

use Psr\Http\Message\ServerRequestInterface;

use Zend\Diactoros\Response;

class PostcodeAction extends AbstractAction
{

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\Postcodes([
            'csrf' => $session['meta']['csrf'],
            'notes' => ($session['notes']) ?? null,
        ]);

        $isUpdate = isset($session['postcodes']);

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            $fields = array_keys($form->getElements() + $form->getFieldsets());

            // Remove these values (initially)
            $fields = array_diff($fields, ['donor-postcode', 'attorney-postcode']);

            $key = 'postcode-options';
            if (isset($data[$key]) && is_array($data[$key]) && count($data[$key]) > 0
            ) {
                // Add back in the selected postcode fields.
                $fields = array_merge($fields, $data[$key]);
            }

            $form->setValidationGroup($fields);

            if ($form->isValid()) {
                $session['postcodes'] = $form->getFormattedData();
                $session['notes'] = $form->getNotes();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            // We are editing previously entered details.
            $form->setData($session['postcodes']);
        } else {
            // Ensure caseworker notes are shown
            $form->setData();
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::postcode-page', [
            'form' => $form,
            'applicant' => $session['applicant'],
            'donor' => $session['donor'],
            'attorney' => $session['attorney']
        ]));
    }
}
