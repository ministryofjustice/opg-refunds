<?php

namespace Auth\Service;

use App\Entity\Cases\User as CaseworkerEntity;
use App\Exception\InvalidInputException;
use App\Service\EntityToModelTrait;
use Auth\Exception\UnauthorizedException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker;
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
        $this->repository = $entityManager->getRepository(CaseworkerEntity::class);
        $this->entityManager = $entityManager;
        $this->tokenTtl = $tokenTtl;
    }

    /**
     * Validate a caseworker password
     *
     * @param string $email
     * @param string $password
     * @return Caseworker
     * @throws UnauthorizedException|Exception
     */
    public function validatePassword(string $email, string $password)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->repository->findOneBy([
            'email' => $email,
        ]);

        if (is_null($caseworker) || $caseworker->getPasswordHash() != hash('sha256', $password)) {
            throw new InvalidInputException('Caseworker not found');
        }

        //  Confirm that the caseworker is active
        if ($caseworker->getStatus() !== Caseworker::STATUS_ACTIVE) {
            throw new UnauthorizedException('User is inactive');
        }

        //  Attempt to generate a token for the caseworker
        do {
            $token = bin2hex(openssl_random_pseudo_bytes(32, $isStrong));

            // Use base62 for shorter tokens
            $token = BigInteger::factory('bcmath')->baseConvert($token, 16, 62);

            if ($isStrong !== true) {
                throw new Exception('Unable to generate a strong token');
            }

            $tokenExpires = time() + $this->tokenTtl;

            $created = $this->setToken($caseworker->getId(), $token, $tokenExpires);
        } while (!$created);

        return $this->translateToDataModel($caseworker);;
    }

    /**
     * Validate a request token
     *
     * @param string $token
     * @return Caseworker
     * @throws UnauthorizedException
     */
    public function validateToken(string $token)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->repository->findOneBy([
            'token' => $token,
        ]);

        //  Confirm that the caseworker is active
        if ($caseworker->getStatus() !== Caseworker::STATUS_ACTIVE) {
            throw new UnauthorizedException('User is inactive');
        }

        //  Check to see if the token has expired
        if (time() > $caseworker->getTokenExpires()) {
            throw new UnauthorizedException('Token expired');
        }

        //  Increase the token expires value
        $this->setToken($caseworker->getId(), $caseworker->getToken(),  time() + $this->tokenTtl);

        return $this->translateToDataModel($caseworker);
    }

    /**
     * Set the token values against a caseworker record
     *
     * @param int $id
     * @param string $token
     * @param int $tokenExpires
     * @return bool
     */
    public function setToken(int $id, string $token, int $tokenExpires)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->entityManager->getReference(CaseworkerEntity::class, $id);

        $caseworker->setToken($token);
        $caseworker->setTokenExpires($tokenExpires);

        $this->entityManager->flush();

        return true;
    }
}
