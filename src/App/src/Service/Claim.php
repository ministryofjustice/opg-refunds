<?php

namespace App\Service;

use App\Entity\Cases\Log;
use Exception;
use Ingestion\Service\DataMigration;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Entity\Cases\User as UserEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class Claim
 * @package App\Service
 */
class Claim
{
    use EntityToModelTrait;

    /**
     * @var EntityRepository
     */
    private $claimRepository;

    /**
     * @var EntityRepository
     */
    private $userRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DataMigration
     */
    private $dataMigrationService;

    /**
     * Claim constructor
     *
     * @param EntityManager $entityManager
     * @param DataMigration $dataMigrationService
     */
    public function __construct(EntityManager $entityManager, DataMigration $dataMigrationService)
    {
        $this->claimRepository = $entityManager->getRepository(ClaimEntity::class);
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->entityManager = $entityManager;
        $this->entityManager = $entityManager;
        $this->dataMigrationService = $dataMigrationService;
    }

    /**
     * Get all claims
     *
     * @return ClaimModel[]
     */
    public function getAll()
    {
        //TODO: Get proper migration running via cron job
        $this->dataMigrationService->migrateAll();

        /** @var ClaimEntity[] $claims */
        $claims = $this->claimRepository->findBy([]);

        return $this->translateToDataModelArray($claims);
    }

    /**
     * Get one claim
     *
     * @param $claimId
     * @return ClaimModel
     */
    public function get($claimId)
    {
        /** @var ClaimEntity $claim */
        $claim = $this->claimRepository->findOneBy([
            'id' => $claimId,
        ]);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
    }

    /**
     * @param int $userId
     * @return array with one element, 'assignedClaimId' which will be the assigned claim id if one was successfully assigned to the user or zero if not
     * @throws Exception
     */
    public function assignNextClaim(int $userId)
    {
        //TODO: Get proper migration running via cron job
        $this->dataMigrationService->migrateOne();

        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy([
            'id' => $userId,
        ]);

        //Using SQL directly to update claim in single atomic call to prevent race conditions
        $statement = $this->entityManager->getConnection()->executeQuery(
            'UPDATE claim SET assigned_to_id = ?, assigned_datetime = NOW(), updated_datetime = NOW(), status = ? WHERE id = (SELECT id FROM claim WHERE assigned_to_id IS NULL AND status = ? ORDER BY received_datetime ASC LIMIT 1) RETURNING id',
            [$user->getId(), ClaimModel::STATUS_IN_PROGRESS, ClaimModel::STATUS_NEW]
        );

        $result = $statement->fetchAll();
        $updateCount = count($result);

        if ($updateCount > 1) {
            throw new Exception("Assigning next claim updated $updateCount rows! It should only update one or zero rows");
        }

        $assignedClaimId = 0;

        if ($updateCount === 1) {
            $assignedClaimId = $result[0]['id'];

            /** @var ClaimEntity $claim */
            $claim = $this->claimRepository->findOneBy([
                'id' => $assignedClaimId,
            ]);

            $log = new Log('Claim started by caseworker', "Caseworker '{$user->getName()}' has begun to process this claim", $claim, $user);
            $claim->addLog($log);

            //$this->casesEntityManager->persist($claim);
            $this->entityManager->flush();
        }

        return ['assignedClaimId' => $assignedClaimId];
    }
}
