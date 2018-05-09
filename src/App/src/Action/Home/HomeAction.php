<?php

namespace App\Action\Home;

use App\Action\AbstractAction;
use App\Form\PhoneClaim;
use App\Form\ProcessNewClaim;
use App\Service\User\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class HomeAction
 * @package App\Action\Home
 */
class HomeAction extends AbstractAction
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserAction constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var User $identity */
        $identity = $request->getAttribute('identity');

        if (in_array(User::ROLE_CASEWORKER, $identity->getRoles())) {
            //  Even though the user details are in the session get them again with a GET call to the API
            $user = $this->userService->getUser($identity->getId());

            $session = $request->getAttribute('session');
            $processNewClaimForm = new ProcessNewClaim([
                'csrf' => $session['meta']['csrf'],
            ]);

            $phoneClaimForm = new PhoneClaim([
                'csrf'  => $session['meta']['csrf'],
            ]);

            return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
                'processNewClaimForm' => $processNewClaimForm,
                'phoneClaimForm' => $phoneClaimForm,
                'user' => $user,
                'messages' => $this->getFlashMessages($request)
            ]));
        } elseif (in_array(User::ROLE_REPORTING, $identity->getRoles())) {
            return $this->redirectToRoute('reporting');
        } elseif (in_array(User::ROLE_REFUND, $identity->getRoles())) {
            return $this->redirectToRoute('refund');
        } elseif (in_array(User::ROLE_ADMIN, $identity->getRoles())) {
            return $this->redirectToRoute('user');
        } elseif (in_array(User::ROLE_QUALITY_CHECKING, $identity->getRoles())) {
            return $this->redirectToRoute('claim.search');
        }

        //No roles!
        return $this->redirectToRoute('sign.out');
    }
}
