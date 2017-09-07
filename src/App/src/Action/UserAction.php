<?php

namespace App\Action;

use App\Exception\InvalidInputException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class UserAction implements ServerMiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $caseworkerId = $request->getAttribute('id');

        if (is_numeric($caseworkerId)) {
            //  TODO - Try to get the details out of the temp constant in the authentication service
            $result = ($caseworkerId == \Auth\Service\AuthenticationService::TEMP_VALID_USER_CREDENTIALS['caseworker_id'] ? \Auth\Service\AuthenticationService::TEMP_VALID_USER_CREDENTIALS : false);

            if (is_array($result)) {
                //  Remove the password value before returning the user details
                unset($result['password']);

                return new JsonResponse($result);
            }
        }

        throw new InvalidInputException('User not found');
    }
}
