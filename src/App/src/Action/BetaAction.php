<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

use App\Service\Refund\Beta\BetaLinkChecker;

class BetaAction extends AbstractAction
{
    private $checker;
    private $cookieName;

    public function __construct(BetaLinkChecker $checker, string $cookieName)
    {
        $this->checker = $checker;
        $this->cookieName = $cookieName;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // Extract details from link...
        $id = $request->getAttribute('betaId');
        $expires = $request->getAttribute('betaExpires');
        $signature = $request->getAttribute('betaSignature');

        //---

        $isValid = $this->checker->isLinkValid($id, $expires, $signature);

        if (!$isValid) {
            return new Response\HtmlResponse( $this->getTemplateRenderer()->render('app::beta-page', [
                'reason' => $isValid
            ]) );
        }

        //---

        /*
         * If we are here, the link is valid.
         * It's details are now stored in a cookie as the user goes through the service.
         * Cookie expiry will equal the link's expiry date.
         */

        // We're redirecting them to the homepage.
        $response = new Response\RedirectResponse($this->getUrlHelper()->generate('home'));

        // Set a cookie with the session ID.
        $response = FigResponseCookies::set($response, SetCookie::create($this->cookieName)
            ->withValue("{$id}-{$expires}-{$signature}")
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withPath('/')
            ->withExpires($expires));

        return $response;
    }
}
