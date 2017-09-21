<?php

namespace App\Action;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use App\Form\ProcessNewClaim;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class HomePageAction
 * @package App\Action
 */
class HomePageAction extends AbstractAction implements ApiClientInterface
{
    use ApiClientTrait;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var User $identity */
        $identity = $request->getAttribute('identity');

        //  Even though the user details are in the session get them again with a GET call to the API
        $userData = $this->getApiClient()->getUser($identity->getId());
        $user = new User($userData);

        $flash = $request->getAttribute('flash');
        $messages = $flash->getMessages();

        $session = $request->getAttribute('session');
        $form = new ProcessNewClaim([
            'csrf' => $session['meta']['csrf'],
        ]);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
            'form'     => $form,
            'user'     => $user,
            'messages' => $messages
        ]));
    }
}
