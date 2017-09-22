<?php

namespace Auth\Service;

use App\Entity\Cases\User as UserEntity;
use App\Exception\InvalidInputException;
use App\Service\EntityToModelTrait;
use Auth\Exception\UnauthorizedException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Zend\Math\BigInteger\BigInteger;
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
     * @var EntityManager
     */
    private $entityManager;

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
     * @param int $tokenTtl
     */
    public function __construct(EntityManager $entityManager, int $tokenTtl)
    {
        $this->repository = $entityManager->getRepository(UserEntity::class);
        $this->entityManager = $entityManager;
        $this->tokenTtl = $tokenTtl;
    }

    /**
     * Validate a user password
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws UnauthorizedException|Exception
     */
    public function validatePassword(string $email, string $password)
    {
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'email' => $email,
        ]);

        if (is_null($user) || $user->getPasswordHash() != hash('sha256', $password)) {
            throw new InvalidInputException('User not found');
        }

        //  Confirm that the user is active
        if ($user->getStatus() !== User::STATUS_ACTIVE) {
            throw new UnauthorizedException('User is inactive');
        }

        //  Attempt to generate a token for the user
        do {
            $token = bin2hex(openssl_random_pseudo_bytes(32, $isStrong));

            // Use base62 for shorter tokens
            $token = BigInteger::factory('bcmath')->baseConvert($token, 16, 62);

            if ($isStrong !== true) {
                throw new Exception('Unable to generate a strong token');
            }

            $tokenExpires = time() + $this->tokenTtl;

            $created = $this->setToken($user->getId(), $token, $tokenExpires);
        } while (!$created);

        return $this->translateToDataModel($user);
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
            'token' => $token,
        ]);

        //  Confirm that the user is active
        if ($user->getStatus() !== User::STATUS_ACTIVE) {
            throw new UnauthorizedException('User is inactive');
        }

        //  Check to see if the token has expired
        if (time() > $user->getTokenExpires()) {
            throw new UnauthorizedException('Token expired');
        }

        //  Increase the token expires value
        $this->setToken($user->getId(), $user->getToken(),  time() + $this->tokenTtl);

        return $this->translateToDataModel($user);
    }

    /**
     * Set the token values against a user record
     *
     * @param int $id
     * @param string $token
     * @param int $tokenExpires
     * @return bool
     */
    public function setToken(int $id, string $token, int $tokenExpires)
    {
        /** @var UserEntity $user */
        $user = $this->entityManager->getReference(UserEntity::class, $id);

        $user->setToken($token);
        $user->setTokenExpires($tokenExpires);

        $this->entityManager->flush();

        return true;
    }
}
