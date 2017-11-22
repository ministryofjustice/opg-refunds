<?php

namespace App\Action;

use App\Form\Notify as NotifyForm;
use App\Service\Notify\Notify as NotifyService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class NotifyAction
 * @package App\Action
 */
class NotifyAction extends AbstractModelAction
{
    /**
     * @var NotifyService
     */
    private $notifyService;

    public function __construct(NotifyService $notifyService)
    {
        $this->notifyService = $notifyService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::notify-page', [
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

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $notified = $this->notifyService->notifyAll();

            $message = "Successfully sent outcome notifications for {$notified['processed']} claims. Query time {$notified['queryTime']}s, notify time {$notified['notifyTime']}s.";

            $remaining = $notified['total'] - $notified['processed'];
            if ($remaining !== 0) {
                $message .= " There are still {$remaining} claims left to send outcome notifications for. Please try again.";
            }

            $this->setFlashInfoMessage($request, $message);

            return $this->redirectToRoute('notify');
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::notify-page', [
            'form'  => $form,
            'messages' => $this->getFlashMessages($request)
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return NotifyForm
     */
    protected function getForm(ServerRequestInterface $request): NotifyForm
    {
        $session = $request->getAttribute('session');

        $form = new NotifyForm([
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}