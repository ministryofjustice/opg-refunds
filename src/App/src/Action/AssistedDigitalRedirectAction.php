<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

use App\Service\User\User as UserService;
use App\Service\AssistedDigital\LinkToken as LinkTokenGenerator;

/**
 * Class ReportingAction
 * @package App\Action
 */
class AssistedDigitalRedirectAction extends AbstractAction
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
     * @param DelegateInterface $delegate
     * @return RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $identity = $request->getAttribute('identity');

        $user = $this->userService->getUser($identity->getId());

        $token = $this->generator->generate([
            'userId' => $user->getId(),
            'name' => $user->getName()
        ]);

        return new RedirectResponse(
            "https://{$this->publicDomain}/assisted-digital/{$token}"
        );
    }
}
