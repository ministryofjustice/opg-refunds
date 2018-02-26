<?php

namespace Auth\Service;

use App\Entity\Cases\User as UserEntity;
use App\Exception\InvalidInputException;
use App\Service\EntityToModelTrait;
use App\Service\User as UserService;
use Auth\Exception\AccountLockedException;
use Auth\Exception\UnauthorizedException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\Cases\User;

/**
 * Class Authentication
 * @package Auth\Service
 */
class Authentication
{
    use EntityToModelTrait;

    const MAX_FAILED_LOGIN_ATTEMPTS = 5;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityManager
     */
    private $entityManager;

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
        $this->entityManager = $entityManager;
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

        if (is_null($user)) {
            throw new InvalidInputException('User not found');
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            $user->setFailedLoginAttempts($user->getFailedLoginAttempts() + 1);
            $this->entityManager->flush();

            if ($user->getFailedLoginAttempts() >= self::MAX_FAILED_LOGIN_ATTEMPTS) {
                throw new AccountLockedException('Account locked');
            }

            throw new InvalidInputException('Invalid password');
        }

        //  Confirm that the user is active
        if ($user->getStatus() !== User::STATUS_ACTIVE) {
            throw new UnauthorizedException('User is inactive');
        }

        if ($user->getFailedLoginAttempts() > 0) {
            if ($user->getFailedLoginAttempts() >= self::MAX_FAILED_LOGIN_ATTEMPTS) {
                throw new AccountLockedException('Account locked');
            }

            $user->setFailedLoginAttempts(0);
            $this->entityManager->flush();
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
