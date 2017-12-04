<?php

namespace App\Action;

use App\Form\Verify as VerifyForm;
use App\Service\Refund\Refund as RefundService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class VerifyAction
 * @package App\Action
 */
class VerifyAction extends AbstractModelAction
{
    /**
     * @var RefundService
     */
    private $refundService;

    public function __construct(RefundService $verifyService)
    {
        $this->refundService = $verifyService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::verify-page', [
            'form'  => $form,
            'messages' => $this->getFlashMessages($request)
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        //Have to use $_FILES directly as Zend validation relies on the form data being in the files format rather than the Zend\Diactoros\UploadedFile provided by $request->getUploadedFiles()
        $post = array_merge_recursive(
            $request->getParsedBody(),
            $_FILES
        );

        $form->setData($post);

        $result = null;

        if ($form->isValid()) {
            $result = $this->refundService->verifyRefundSpreadsheet($request->getUploadedFiles()['spreadsheet']);

            if ($result['valid'] === true) {
                $this->setFlashInfoMessage($request, 'Spreadsheet is valid and has not been altered');

                return $this->redirectToRoute('verify');
            } else {
                $form->setMessages(['spreadsheet' => ['WARNING Spreadsheet has been altered!']]);
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::verify-page', [
            'form'  => $form,
            'messages' => $this->getFlashMessages($request),
            'result' => $result
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return VerifyForm
     */
    protected function getForm(ServerRequestInterface $request): VerifyForm
    {
        $session = $request->getAttribute('session');

        $form = new VerifyForm([
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}