<?php

namespace App\Action;

use App\Form\Notify as NotifyForm;
use App\Service\Notify\Notify as NotifyService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

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
     * @return HtmlResponse|RedirectResponse
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        $form->setData($request->getParsedBody());

        $letters = [];

        if ($form->isValid()) {
            $notified = $this->notifyService->notifyAll();
            $letters = $notified['letters'];
            $lettersCount = count($letters);

            if ($notified['total'] === 0) {
                $message = 'No outcome notifications needed sending. Please try again later.';
            } else {
                $message = '';

                if ($notified['processed'] > 0) {
                    $claimText = $notified['processed'] === 1 ? 'claim' : 'claims';
                    $message = "Successfully sent outcome notifications for {$notified['processed']} {$claimText}.";
                }

                $remaining = $notified['total'] - $notified['processed'];
                if ($remaining !== 0) {
                    if (empty($message) === false) {
                        $message .= ' ';
                    }

                    if ($remaining === 1) {
                        $message .= "There is {$remaining} claim left to send outcome notifications for";
                    } else {
                        $message .= "There are {$remaining} claims left to send outcome notifications for";
                    }

                    if ($remaining === $lettersCount) {
                        //Only letters remaining
                        $message .= ' by post. See list below for required postal notifications.';
                    } elseif ($lettersCount > 0) {
                        $message .= " including {$lettersCount} by post. Please try again to send remaining email and text notifications and see list below for required postal notifications.";
                    } else {
                        //No letters required
                        $message .= '. Please try again to send remaining email and text notifications.';
                    }
                }
            }

            $this->setFlashInfoMessage($request, $message, $lettersCount > 0);

            if ($lettersCount === 0) {
                return $this->redirectToRoute('notify');
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::notify-page', [
            'form'  => $form,
            'messages' => $this->getFlashMessages($request),
            'letters' => $letters
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