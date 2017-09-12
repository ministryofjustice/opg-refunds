<?php

namespace Applications\Service;

use App\Crypt\Hybrid as HybridCipher;
use App\Entity\Cases\RefundCase;
use Applications\Entity\Application;
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
        $this->caseRepository = $this->casesEntityManager->getRepository(RefundCase::class);
        $this->fullCipher = $fullCipher;
    }

    /**
     * @return Application
     */
    public function getNextApplication(): Application
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
        $data = stream_get_contents($application->getData());
        $json = gzinflate($this->fullCipher->decrypt($data));
        return $json;
    }

    /**
     * @param Application $application
     * @return RefundCase
     */
    public function getRefundCase(Application $application): RefundCase
    {
        $decryptedData = $this->getDecryptedData($application);
        $applicationData = json_decode($decryptedData, true);
        $donorName = "{$applicationData['donor']['name']['title']} {$applicationData['donor']['name']['first']} {$applicationData['donor']['name']['last']}";
        $refundCase = new RefundCase($application->getId(), $application->getCreated(), $decryptedData, $donorName);
        return $refundCase;
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
            $refundCase = $this->getRefundCase($application);

            $this->casesEntityManager->persist($refundCase);
            $this->casesEntityManager->flush();
        }

        return false;
    }
}