<?php

namespace Auth\Service;

use App\Entity\Cases\User as UserEntity;
use App\Exception\InvalidInputException;
use App\Service\EntityToModelTrait;
use App\Service\User as UserService;
use Auth\Exception\UnauthorizedException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Exception;

/**
 * Class Authentication
 * @package Auth\Service
 */
class Authentication
{
    use EntityToModelTrait;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * The number of seconds before an auth token expires
     *
     * @var int
     */
    private $tokenTtl;

    /**
     * Authentication constructor
     *
     * @param EntityManager $entityManager
     * @param UserService $userService
     * @param int $tokenTtl
     */
    public function __construct(EntityManager $entityManager, UserService $userService, int $tokenTtl)
    {
        $this->repository = $entityManager->getRepository(UserEntity::class);
        $this->userService = $userService;
        $this->tokenTtl = $tokenTtl;
    }

    /**
     * Validate a user password
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws InvalidInputException|UnauthorizedException
     */
    public function validatePassword(string $email, string $password)
    {
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'email' => $email,
            'status' => User::STATUS_ACTIVE,
        ]);

        if (is_null($user) || !password_verify($password, $user->getPasswordHash())) {
            throw new InvalidInputException('User not found');
        }

        //  Confirm that the user is active
        if ($user->getStatus() !== User::STATUS_ACTIVE) {
            throw new UnauthorizedException('User is inactive');
        }

        //  Use the user service to set a token for the user
        return $this->userService->refreshToken($user->getId(), time() + $this->tokenTtl);
    }

    /**
     * Validate a request token
     *
     * @param string $token
     * @return User
     * @throws UnauthorizedException
     */
    public function validateToken(string $token)
    {
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'token'  => $token,
            'status' => User::STATUS_ACTIVE,
        ]);

        if (is_null($user)) {
            throw new UnauthorizedException('Bad token');
        }

        //  Check to see if the token has expired
        if (time() > $user->getTokenExpires()) {
            throw new UnauthorizedException('Token expired');
        }

        //  Increase the token expires value - and return the user model
        return $this->userService->refreshToken($user->getId(), time() + $this->tokenTtl, false);
    }
}
