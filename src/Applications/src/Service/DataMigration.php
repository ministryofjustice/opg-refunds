<?php

namespace Applications\Service;

use App\Crypt\Hybrid as HybridCipher;
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
     * @var HybridCipher
     */
    private $fullCipher;

    /**
     * DataMigration constructor.
     * @param EntityManager $applicationsEntityManager
     * @param HybridCipher $fullCipher
     */
    public function __construct(EntityManager $applicationsEntityManager, HybridCipher $fullCipher)
    {
        $this->applicationsEntityManager = $applicationsEntityManager;
        $this->applicationRepository = $this->applicationsEntityManager->getRepository(Application::class);
        $this->fullCipher = $fullCipher;
    }

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
}