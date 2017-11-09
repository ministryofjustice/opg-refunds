<?php
namespace App\Middleware\AssistedDigital;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;

use App\Service\Refund\AssistedDigital\LinkToken;

class AssistedDigitalMiddleware implements ServerMiddlewareInterface
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
        $cookies = $request->getCookieParams();

        try {

            if (isset($cookies[$this->cookieName])) {

                $value = $cookies[$this->cookieName];

                $payload = $this->checker->verify($value);

                $request = $request->withAttribute('ad', $payload);

            }

        } catch (\Exception $e) {
        }

        return $delegate->process($request);
    }

}
