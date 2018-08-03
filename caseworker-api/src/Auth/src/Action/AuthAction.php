<?php

namespace Auth\Action;

use App\Exception\InvalidInputException;
use Auth\Exception\UnauthorizedException;
use Auth\Service\Authentication;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

use Opg\Refunds\Log\Initializer;

/**
 * Class AuthAction
 * @package Auth\Action
 */
class AuthAction implements RequestHandlerInterface, Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

    /**
     * @var Authentication
     */
    private $authService;

    /**
     * AuthAction constructor
     *
     * @param Authentication $authService
     */
    public function __construct(Authentication $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws UnauthorizedException
     */
    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $requestBody = $request->getParsedBody();

        if (isset($requestBody['email']) && isset($requestBody['password'])) {
            try {
                $user = $this->authService->validatePassword($requestBody['email'], $requestBody['password']);

                //  Get the user details excluding the claims
                $userData = $user->getArrayCopy([
                    'claims',
                ]);

                $this->getLogger()->info('Caseworker login: ' . $userData['name'], ['userId'=>$userData['id']]);

                return new JsonResponse($userData);
            } catch (InvalidInputException $ignore) {
                //  Authentication failed - exception thrown below
            }
        }

        $this->getLogger()->warn('Failed authentication attempt', ['email'=>strtolower($requestBody['email'])]);

        throw new UnauthorizedException('Authentication failed');
    }
}
