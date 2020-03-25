<?php

namespace App\Action;

use App\Form\PhoneClaim;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;

use App\Service\User\User as UserService;
use App\Service\AssistedDigital\LinkToken as LinkTokenGenerator;

/**
 * Class ReportingAction
 * @package App\Action
 */
class PhoneClaimAction extends AbstractModelAction
{
    /**
     * @var LinkTokenGenerator
     */
    protected $generator;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var string
     */
    protected $publicDomain;


    /**
     * UserAction constructor.
     * @param LinkTokenGenerator $generator
     * @param UserService $userService
     * @param string $publicDomain
     */
    public function __construct(LinkTokenGenerator $generator, UserService $userService, string $publicDomain)
    {
        $this->generator = $generator;
        $this->userService = $userService;
        $this->publicDomain = $publicDomain;
    }

    /**
     * @param ServerRequestInterface $request
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::phone-claim-page', [
            'form'  => $form
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return HtmlResponse|RedirectResponse
     */
    public function addAction(ServerRequestInterface $request)
    {
        $form = $this->getForm($request);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $formData = $form->getData();

            $type = $formData['type'];

            $identity = $request->getAttribute('identity');

            $user = $this->userService->getUser($identity->getId());

            $token = $this->generator->generate([
                'userId' => $user->getId(),
                'name' => $user->getName(),
                'type' => $type
            ]);

            return new RedirectResponse(
                "https://{$this->publicDomain}/assisted-digital/{$token}"
            );
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::phone-claim-page', [
            'form'  => $form
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return PhoneClaim
     */
    protected function getForm(ServerRequestInterface $request): PhoneClaim
    {
        $session = $request->getAttribute('session');

        $form = new PhoneClaim([
            'csrf'  => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
