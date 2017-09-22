<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
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

        if (is_string($isValid)) {
            /*
             * Possible reasons:
             * - no-cookie
             * - link-used
             * - missing-data
             * - expired
             * - invalid-signature
             */
            switch ($isValid) {
                case 'no-cookie':
                    $page = 'app::beta-unavailable-page';
                    break;
                case 'link-used':
                    $page = 'app::beta-submitted-page';
                    break;
                case 'expired':
                    $page = 'app::beta-expired-page';
                    break;
                default:
                    $page = 'app::beta-invalid-page';
            }

            // Display the error
            return new Response\HtmlResponse($this->getTemplateRenderer()->render($page));
        }

        //--------------------
        // Cookie check

        $cookies = $request->getCookieParams();
        $query = $request->getQueryParams();

        $haveCookie = isset($cookies[$this->cookieName]);
        $haveQuery = isset($query['cookies']);

        // If we don't have a cookie AND the user has not already been redirect
        // Redirect them, ensuring we send the cookie.
        if (!$haveCookie && !$haveQuery) {
            $response = new Response\RedirectResponse($this->getUrlHelper()->generate().'?cookies=1');
            return $this->addCookieToResponse($response, $id, $expires, $signature);
        }

        // If we still don't have a cookie after the user has been redirected
        // Display the cookies disabled page.
        if (!$haveCookie && $haveQuery) {
            return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::cookies-disabled-page'));
        }

        //--------------------

        /*
         * If we are here, the link is valid.
         * It's details are now stored in a cookie as the user goes through the service.
         * Cookie expiry will equal the link's expiry date.
         */

        // We're redirecting them to the homepage.
        $response = new Response\RedirectResponse($this->getUrlHelper()->generate('home'));

        return $this->addCookieToResponse($response, $id, $expires, $signature);
    }

    /**
     * Added the beta cookie to the passed response.
     *
     * @param Response $response
     * @param $id
     * @param $expires
     * @param $signature
     * @return ResponseInterface
     */
    private function addCookieToResponse(Response $response, $id, $expires, $signature) : ResponseInterface
    {
        return FigResponseCookies::set($response, SetCookie::create($this->cookieName)
            ->withValue("{$id}-{$expires}-{$signature}")
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withPath('/')
            ->withExpires($expires));
    }
}
