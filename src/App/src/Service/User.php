<?php

namespace App\Service;

use App\Entity\Cases\User as UserEntity;
use App\Exception\AlreadyExistsException;
use App\Exception\InvalidInputException;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Opg\Refunds\Caseworker\DataModel\Cases\UserSummary as UserSummaryModel;
use Opg\Refunds\Caseworker\DataModel\Cases\UserSummaryPage;

/**
 * Class User
 * @package App\Service
 */
class User
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
     * @var TokenGenerator
     */
    private $tokenGenerator;

    /**
     * User constructor
     *
     * @param EntityManager $entityManager
     * @param TokenGenerator $tokenGenerator
     */
    public function __construct(EntityManager $entityManager, TokenGenerator $tokenGenerator)
    {
        $this->repository = $entityManager->getRepository(UserEntity::class);
        $this->entityManager = $entityManager;
        $this->tokenGenerator = $tokenGenerator;
    }
    
    /**
     * Search all users
     *
     * @param int|null $page
     * @param int|null $pageSize
     * @param string|null $search
     * @param string|null $status
     * @param string|null $orderBy
     * @param string|null $sort
     * @return UserSummaryPage
     */
    public function search(
        int $page = null,
        int $pageSize = null,
        string $search = null,
        string $status = null,
        string $orderBy = null,
        string $sort = null
    ) {
        if ($page !== null && $page < 1) {
            $page = 1;
        }

        if ($orderBy === null) {
            $orderBy = 'name';
        }

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from('Cases:User', 'u');

        $parameters = [];

        if (isset($search)) {
            $userName = $search;
            $queryBuilder->andWhere('LOWER(u.name) LIKE LOWER(:userName)');
            $parameters['userName'] = "%{$userName}%";
        }

        if (isset($status)) {
            $queryBuilder->andWhere('u.status = :status');
            $parameters['status'] = $status;
        }

        if (isset($orderBy)) {
            $sort = strtoupper($sort ?: 'asc');

            if ($orderBy === 'name') {
                $queryBuilder->orderBy('u.name', $sort);
            } elseif ($orderBy === 'email') {
                $queryBuilder->orderBy('u.email', $sort);
            }
        }

        // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/pagination.html
        $queryBuilder
            ->setParameters($parameters)
            ->setMaxResults($pageSize);

        if ($page !== null) {
            $offset = ($page - 1) * $pageSize;
            $queryBuilder->setFirstResult($offset);
        }

        if ($pageSize !== null) {
             $queryBuilder->setFirstResult($pageSize);
        }

        $paginator = new Paginator($queryBuilder, true);

        $total = count($paginator);
        $pageCount = $pageSize === null ? 1 : ceil($total/$pageSize);

        $userSummaries = [];

        foreach ($paginator as $user) {
            $userSummaries[] = $this->translateToDataModel($user, [], UserSummaryModel::class);
        }

        $userSummaryPage = new UserSummaryPage();
        $userSummaryPage
            ->setPage($page ?: 1)
            ->setPageSize($pageSize ?: $total)
            ->setPageCount($pageCount)
            ->setTotal($total)
            ->setUserSummaries($userSummaries);

        return $userSummaryPage;
    }

    /**
     * Get all non-deleted users
     *
     * @return UserModel[]
     */
    public function getAll()
    {
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->neq('status', UserModel::STATUS_DELETED))
                 ->orderBy(['name' => 'ASC']);

        $result = $this->repository->matching($criteria);
        $users = $result->getValues();

        return $this->translateToDataModelArray($users);
    }

    /**
     * Get a specific user
     *
     * @param int $id
     * @return UserModel
     */
    public function getById(int $id)
    {
        $user = $this->getUserEntity($id);

        return $this->translateToDataModel($user);
    }

    /**
     * Get a specific user
     *
     * @param array $ids
     * @return UserModel[]
     */
    public function getByIds(array $ids)
    {
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->in('id', $ids))
            ->orderBy(['name' => 'ASC']);

        $result = $this->repository->matching($criteria);
        $users = $result->getValues();

        return $this->translateToDataModelArray($users);
    }

    /**
     * Get a specific active user by email address
     *
     * @param string $email
     * @return UserModel
     */
    public function getByEmail(string $email)
    {
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'email'  => $email,
            'status' => UserModel::STATUS_ACTIVE,
        ]);

        return $this->translateToDataModel($user);
    }

    /**
     * Get a specific user
     *
     * @param string $token
     * @param bool $asPasswordToken
     * @return UserModel
     */
    public function getByToken(string $token, bool $asPasswordToken = false)
    {
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'token' => $token,
        ]);

        //  If the token is being treated as a password token then ensure that the token expiry value is set as -1
        if ($asPasswordToken && $user instanceof UserEntity && $user->getTokenExpires() > 0) {
            throw new InvalidInputException('Token can not be used as a password token');
        }

        return $this->translateToDataModel($user);
    }

    /**
     * Add a user
     *
     * @param UserModel $userModel
     * @return UserModel
     */
    public function add(UserModel $userModel)
    {
        $this->checkForExisting($userModel->getEmail());

        $user = new UserEntity();
        $user->setFromDataModel($userModel);

        //  Set the new user with a token that can be used to set the password the first time
        $user->setToken($this->tokenGenerator->generate());
        $user->setTokenExpires(-1);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->translateToDataModel($user);
    }

    /**
     * @param $userId
     * @param $name
     * @return UserModel
     */
    public function setName($userId, $name)
    {
        $user = $this->getUserEntity($userId);

        $user->setName($name);

        $this->entityManager->flush();

        return $this->translateToDataModel($user);
    }

    /**
     * @param $userId
     * @param $email
     * @return UserModel
     */
    public function setEmail($userId, $email)
    {
        $this->checkForExisting($email, $userId);

        $user = $this->getUserEntity($userId);

        $user->setEmail($email);

        $this->entityManager->flush();

        return $this->translateToDataModel($user);
    }

    /**
     * @param $userId
     * @param $roles
     * @return UserModel
     */
    public function setRoles($userId, $roles)
    {
        $user = $this->getUserEntity($userId);

        //  Translate roles array to string for entity
        $roles = implode(',', $roles);

        $user->setRoles($roles);

        $this->entityManager->flush();

        return $this->translateToDataModel($user);
    }

    /**
     * @param $userId
     * @param $status
     * @return UserModel
     */
    public function setStatus($userId, $status)
    {
        $user = $this->getUserEntity($userId);

        $user->setStatus($status);

        $this->entityManager->flush();

        return $this->translateToDataModel($user);
    }

    /**
     * Refresh the user token by updating the value or adjusting the expiry (or both)
     *
     * @param $userId
     * @param $tokenExpires
     * @param bool $generateNew
     * @return UserModel
     */
    public function refreshToken($userId, $tokenExpires, $generateNew = true)
    {
        $user = $this->getUserEntity($userId);

        //  If required generate a new token value
        if ($generateNew) {
            $user->setToken($this->tokenGenerator->generate());
        }

        $user->setTokenExpires($tokenExpires);

        $this->entityManager->flush();

        return $this->translateToDataModel($user);
    }

    /**
     * Set the hashed password and activate the account
     *
     * @param $userId
     * @param $password
     * @return UserModel
     */
    public function setPassword($userId, $password)
    {
        $user = $this->getUserEntity($userId);

        $user->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));
        $user->setStatus(UserModel::STATUS_ACTIVE);

        $this->entityManager->flush();

        return $this->translateToDataModel($user);
    }

    /**
     * Check for an existing user using this email address - excluding the user if an ID value is provided
     *
     * @param string $email
     * @param int $userId
     * @throws AlreadyExistsException
     */
    private function checkForExisting(string $email, int $userId = null)
    {
        $predicates = 'u.email = :email AND u.status <> :status';

        $params = [
            'email'  => $email,
            'status' => UserModel::STATUS_DELETED,
        ];

        if (!is_null($userId)) {
            $predicates = 'u.id <> :id AND ' . $predicates;
            $params['id'] = $userId;
        }

        $queryBuilder = $this->repository->createQueryBuilder('u');

        $queryBuilder->where($predicates)
            ->setParameters($params);

        $users = $queryBuilder->getQuery()->getResult();

        if (count($users) > 0) {
            throw new AlreadyExistsException('Email address already exists');
        }
    }

    /**
     * @param $id
     * @return UserEntity
     */
    private function getUserEntity($id): UserEntity
    {
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'id' => $id,
        ]);

        return $user;
    }
}
