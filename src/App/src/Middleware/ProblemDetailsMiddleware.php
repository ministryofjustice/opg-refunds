<?php

namespace App\Middleware;

use App\Exception\AbstractApiException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ProblemDetailsMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Psr\Http\Message\ResponseInterface|JsonResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        try {
            $response = $delegate->process($request);

            return $response;
        } catch (AbstractApiException $ex) {
            //  Translate this exception type into response JSON
            $problem = array_merge([
                'title' => $ex->getTitle(),
                'detail' => $ex->getMessage(),
            ], $ex->getAdditionalData());

            return new JsonResponse($problem, $ex->getCode(), [
                'Content-Type' => 'application/problem+json',
            ]);
        }
    }
}
