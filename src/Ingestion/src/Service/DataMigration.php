<?php

namespace Ingestion\Service;

use App\Crypt\Hybrid as HybridCipher;
use App\Entity\Cases\Claim;
use Ingestion\Entity\Application;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class DataMigration
{
    /**
     * @var EntityManager
     */
    private $applicationsEntityManager;

    /**
     * @var EntityRepository
     */
    private $applicationRepository;

    /**
     * @var EntityManager
     */
    private $casesEntityManager;

    /**
     * @var EntityRepository
     */
    private $caseRepository;

    /**
     * @var HybridCipher
     */
    private $fullCipher;

    /**
     * DataMigration constructor.
     * @param EntityManager $applicationsEntityManager
     * @param HybridCipher $fullCipher
     */
    public function __construct(EntityManager $applicationsEntityManager, EntityManager $casesEntityManager, HybridCipher $fullCipher)
    {
        $this->applicationsEntityManager = $applicationsEntityManager;
        $this->applicationRepository = $this->applicationsEntityManager->getRepository(Application::class);
        $this->casesEntityManager = $casesEntityManager;
        $this->caseRepository = $this->casesEntityManager->getRepository(Claim::class);
        $this->fullCipher = $fullCipher;
    }

    /**
     * @return Application
     */
    public function getNextApplication()
    {
        /** @var Application $application */
        $application = $this->applicationRepository->findOneBy(['processed' => false], ['created' => 'DESC']);
        return $application;
    }

    /**
     * @param Application $application
     * @return string Application JSON
     */
    public function getDecryptedData(Application $application): string
    {
        $json = gzinflate($this->fullCipher->decrypt($application->getData()));
        return $json;
    }

    /**
     * @param Application $application
     * @return Claim
     */
    public function getClaim(Application $application): Claim
    {
        $decryptedData = $this->getDecryptedData($application);
        $applicationData = json_decode($decryptedData, true);
        $donorName = "{$applicationData['donor']['name']['title']} {$applicationData['donor']['name']['first']} {$applicationData['donor']['name']['last']}";
        $claim = new Claim($application->getId(), $application->getCreated(), $decryptedData, $donorName);
        return $claim;
    }

    /**
     * Migrate a single application to the cases database
     *
     * @return bool true if migration was successful
     */
    public function migrateOne(): bool
    {
        $application = $this->getNextApplication();
        if ($application !== null) {
            $claim = $this->getClaim($application);

            if ($this->caseRepository->findOneBy(['id' => $claim->getId()]) === null) {
                try {
                    $this->casesEntityManager->persist($claim);
                    $this->casesEntityManager->flush();

                    $this->setProcessed($application);

                } catch (UniqueConstraintViolationException $ex) {
                    $this->setProcessed($application);
                }
            } else {
                $this->setProcessed($application);
            }

            return true;
        }

        return false;
    }

    public function migrateAll()
    {
        $migrationCounter = 0;

        while ($this->migrateOne()) {
            $migrationCounter++;
        };

        return $migrationCounter;
    }

    /**
     * @param Application $application
     */
    private function setProcessed(Application $application)
    {
        $application->setProcessed(true);
        $this->applicationsEntityManager->persist($application);
        $this->applicationsEntityManager->flush();
    }
}