<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

class CookiesCheckAction extends AbstractAction
{

    const COOKIE_NAME = 'cookies_enabled';

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $cookies = $request->getCookieParams();

        // If the cookie is there, forward to next page.
        if (isset($cookies[self::COOKIE_NAME])) {
            return new Response\RedirectResponse(
                $this->getUrlHelper()->generate('apply.who')
            );
        }


        //-----------------------------------------------------------
        // Add the cookie and reload the page to ensure it's returned

        $query = $request->getQueryParams();

        if (!isset($query['cookies'])) {
            $response = new Response\RedirectResponse($this->getUrlHelper()->generate().'?cookies=1');

            $response = FigResponseCookies::set($response, SetCookie::create(self::COOKIE_NAME)
                ->withValue("yes")
                ->withSecure(true)
                ->withHttpOnly(true)
                ->withPath('/')
                ->withExpires(new \DateTime("+1 month")));

            return $response;
        }

        // If we get here, the cookie should have been set, but we still can't see this.
        // This display the cookies not enabled page.

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::cookies-disabled-page'));
    }
}
