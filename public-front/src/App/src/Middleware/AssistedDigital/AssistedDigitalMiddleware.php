<?php
namespace App\Middleware\AssistedDigital;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;

use App\Service\Refund\AssistedDigital\LinkToken;

use League\Plates\Engine as PlatesEngine;

/**
 * Checks for and verifies an Assisted Digital cookie.
 * If all is good, the token's payload is added into the ServerRequest.
 *
 * Class AssistedDigitalMiddleware
 * @package App\Middleware\AssistedDigital
 */
class AssistedDigitalMiddleware implements ServerMiddlewareInterface
{
    private $checker;
    private $cookieName;
    private $plates;

    public function __construct(LinkToken $checker, string $cookieName, PlatesEngine $plates)
    {
        $this->checker = $checker;
        $this->cookieName = $cookieName;
        $this->plates = $plates;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $cookies = $request->getCookieParams();

        $isDonorDeceased = false;

        try {
            if (isset($cookies[$this->cookieName])) {
                $value = $cookies[$this->cookieName];

                $payload = $this->checker->verify($value);

                $request = $request->withAttribute('ad', $payload);

                $isDonorDeceased = !is_null($payload)
                    && array_key_exists('type', $payload) && $payload['type'] == 'donor_deceased';
            }

        } catch (\Exception $e) {
        }

        $request = $request->withAttribute('isDonorDeceased', $isDonorDeceased);

        $this->plates->addData([
            'ad'=> ($payload) ?? []
        ]);

        return $delegate->process($request);
    }
}
