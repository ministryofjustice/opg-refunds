<?php

namespace App\Action;

use App\Form\Caseworker;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker as CaseworkerModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use Zend\Form\FormInterface;

/**
 * Class CaseworkerAction
 * @package App\Action
 */
class CaseworkerAction extends AbstractRestfulAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $caseworkerId = $request->getAttribute('id');

        if (is_numeric($caseworkerId)) {
            $caseworkerData = $this->getApiClient()->getCaseworker($caseworkerId);

            $caseworker = new CaseworkerModel($caseworkerData);

            return new HtmlResponse($this->getTemplateRenderer()->render('app::caseworker-page', [
                'caseworker' => $caseworker,
            ]));
        }

        //  Get all caseworkers
        $caseworkers = [];

        //  Even though the caseworker details are in the session get them again with a GET call to the API
        $caseworkersData = $this->getApiClient()->getCaseworkers();

        foreach ($caseworkersData as $caseworkerData) {
            $caseworkers[] = new CaseworkerModel($caseworkerData);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::caseworkers-page', [
            'caseworkers' => $caseworkers,
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $caseworkerId = $request->getAttribute('id');
        $caseworkerData = [];

        if (is_numeric($caseworkerId)) {
            $caseworkerData = $this->getApiClient()->getCaseworker($caseworkerId);
        }

        if (empty($caseworkerData)) {
            throw new Exception('Page not found', 404);
        }

        $caseworker = new CaseworkerModel($caseworkerData);

        $session = $request->getAttribute('session');

        //  There is no active session so continue
        $form = new Caseworker([
            'csrf' => $session['meta']['csrf']
        ]);

        $authenticationError = null;

        if ($request->getMethod() == 'POST') {
            //  TODO - Handle form validation and post here
        } else {
            $form->bind($caseworker);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::caseworker-edit-page', [
            'caseworker' => $caseworker,
            'form'       => $form,
            'error'      => $authenticationError,
        ]));
    }
}
