<?php
namespace App\Action;

use Psr\Http\Message\ServerRequestInterface;

use Laminas\Diactoros\Response;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

class CookiesCheckAction extends AbstractAction
{

    const COOKIE_NAME = 'cookies_enabled';

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {

        $cookies = $request->getCookieParams();

        // If the cookie is there, forward to next page.
        if (isset($cookies[self::COOKIE_NAME])) {
            $response = new Response\RedirectResponse(
                $this->getUrlHelper()->generate('apply.who')
            );

            // If the 'complete' cookie is set, take this chance to remove it.
            if (isset($cookies['complete'])) {
                $response = FigResponseCookies::set($response, SetCookie::createExpired('complete'));
            }

            return $response;
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
