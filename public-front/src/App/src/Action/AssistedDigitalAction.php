<?php
namespace App\Action;

use DateTime;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

use App\Service\Refund\AssistedDigital\LinkToken;

class AssistedDigitalAction extends AbstractAction
{
    private $checker;
    private $cookieName;

    public function __construct(LinkToken $checker, string $cookieName)
    {
        $this->checker = $checker;
        $this->cookieName = $cookieName;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        // Clear out any existing session data if there is any.
        $session->clear();

        //---

        $token = $request->getAttribute('token');

        try {
            // An exception is thrown if the token is invalid.
            $this->checker->verify($token);
        } catch (\Exception $e) {
            return new Response\HtmlResponse(
                $this->getTemplateRenderer()->render('app::assisted-digital-invalid-page')
            );
        }

        //---

        // We're redirecting them to the homepage.
        $response = new Response\RedirectResponse($this->getUrlHelper()->generate('start'));

        $response = FigResponseCookies::set($response, SetCookie::create($this->cookieName)
            ->withValue($token)
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withPath('/')
            ->withExpires(new DateTime('tomorrow -1 second')));

        return $response;
    }
}
