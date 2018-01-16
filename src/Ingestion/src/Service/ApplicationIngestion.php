<?php

namespace Ingestion\Service;

use App\Entity\Cases\Claim;
use App\Entity\Cases\Note;
use App\Entity\Cases\User;
use Ingestion\Entity\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Log\Initializer;

class ApplicationIngestion implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

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
    private $claimRepository;

    /**
     * @var EntityRepository
     */
    private $userRepository;

    /**
     * DataMigration constructor.
     * @param EntityManager $applicationsEntityManager
     * @param EntityManager $casesEntityManager
     */
    public function __construct(EntityManager $applicationsEntityManager, EntityManager $casesEntityManager)
    {
        $this->applicationsEntityManager = $applicationsEntityManager;
        $this->applicationRepository = $this->applicationsEntityManager->getRepository(Application::class);
        $this->casesEntityManager = $casesEntityManager;
        $this->claimRepository = $this->casesEntityManager->getRepository(Claim::class);
        $this->userRepository = $this->casesEntityManager->getRepository(User::class);
    }

    /**
     * @return Application
     */
    public function getNextApplication()
    {
        /** @var Application $application */
        $application = $this->applicationRepository->findOneBy(['processed' => false], ['created' => 'ASC']);
        return $application;
    }

    /**
     * Decompress data from Public Front.
     *
     * @param Application $application
     * @return string Application JSON
     */
    public function getDecompressedData(Application $application): string
    {
        try {
            return gzinflate($application->getData());
        } catch (\Throwable $e) {
            /*
             * Data used to be encrypted when it reached this point, but that's no longer the case.
             * If decompression did fail, it's possible this function was trying to process legacy data.
             * In reality, this should never be the case.
             */
            throw new \UnexpectedValueException('Unable to decompress payload. Maybe it was encrypted?');
        }
    }

    /**
     * @param Application $application
     * @return array
     */
    public function getApplicationData(Application $application): array
    {
        $uncompressedData = $this->getDecompressedData($application);
        $applicationData = json_decode($uncompressedData, true);
        return $applicationData;
    }

    /**
     * @param Application $application
     * @return Claim
     */
    public function getClaim(Application $application): Claim
    {
        $uncompressedData = $this->getDecompressedData($application);
        $applicationData = json_decode($uncompressedData, true);

        $donorCurrentName = $applicationData['donor']['current']['name'];
        $donorName = "{$donorCurrentName['title']} {$donorCurrentName['first']} {$donorCurrentName['last']}";

        $accountHash = isset($applicationData['account']) ? $applicationData['account']['hash'] : null;

        $claim = new Claim(
            $application->getId(),
            $application->getCreated(),
            $applicationData,
            $donorName,
            $accountHash
        );

        return $claim;
    }

    public function ingestAllApplication()
    {
        $count = 0;

        while ($this->ingestApplication()) {
            $count++;
        }

        return $count;
    }

    /**
     * Ingest a single application and copy it to the cases database
     *
     * @return bool true if ingestion was successful
     */
    public function ingestApplication(): bool
    {
        $application = $this->getNextApplication();
        if ($application !== null) {
            $claim = $this->getClaim($application);
            $this->casesEntityManager->persist($claim);

            if ($this->claimRepository->findOneBy(['id' => $claim->getId()]) === null) {
                try {
                    $applicationData = $this->getApplicationData($application);

                    $isAssistedDigital = isset($applicationData['ad']);

                    $applicantName = '';
                    if ($applicationData['applicant'] === 'donor') {
                        $applicantName = $claim->getDonorName() . ' (Donor)';
                    } elseif ($applicationData['applicant'] === 'attorney') {
                        $applicantName = "{$applicationData['attorney']['current']['name']['title']} {$applicationData['attorney']['current']['name']['first']} {$applicationData['attorney']['current']['name']['last']} (Attorney)";
                    }

                    $receivedDateString = date('d M Y \a\t H:i', $claim->getReceivedDateTime()->getTimestamp());
                    $note = new Note(NoteModel::TYPE_CLAIM_SUBMITTED, "Claim submitted by $applicantName on $receivedDateString", $claim);
                    $this->casesEntityManager->persist($note);

                    if ($isAssistedDigital) {
                        $adUserId = $applicationData['ad']['meta']['userId'];

                        /** @var User $user */
                        $adUser = $this->userRepository->findOneBy([
                            'id' => $adUserId,
                        ]);

                        $adNotes = $applicationData['ad']['notes'];
                        $adNote = new Note(NoteModel::TYPE_ASSISTED_DIGITAL, $adNotes, $claim, $adUser);
                        $this->casesEntityManager->persist($adNote);
                    }

                    $this->casesEntityManager->flush();

                    $this->setProcessed($application);

                    $this->getLogger()->info("Application with id {$claim->getId()} was successfully ingested");
                } catch (UniqueConstraintViolationException $ex) {
                    // Doctrine 2’s EntityManager class will permanently close connections upon failed transactions
                    if (!$this->casesEntityManager->isOpen()) {
                        // So check if this is the case and recreate if so
                        $this->getLogger()->warn('Cases entity manager was permanently closed after failed transation. Recreating');

                        $this->casesEntityManager = $this->casesEntityManager->create(
                            $this->casesEntityManager->getConnection(),
                            $this->casesEntityManager->getConfiguration()
                        );

                        $this->claimRepository = $this->casesEntityManager->getRepository(Claim::class);
                        $this->userRepository = $this->casesEntityManager->getRepository(User::class);

                        $this->getLogger()->info(' Successfully recreated cases entity manager');
                    }

                    $this->setProcessed($application);

                    $this->getLogger()->warn("Application with id {$claim->getId()} was attempted to be ingested at least twice violating a unique constraint in the database. It will have been ingested successfully by another worker");
                }
            } else {
                $this->setProcessed($application);

                $this->getLogger()->info("Application with id {$claim->getId()} has previously been successfully ingested");
            }

            return true;
        }

        return false;
    }

    /**
     * @param Application $application
     */
    private function setProcessed(Application $application)
    {
        //Null data to make sure no data is left in database potentially accessible on the public internet
        $application->setData(null);
        $application->setProcessed(true);
        $this->applicationsEntityManager->persist($application);
        $this->applicationsEntityManager->flush();
    }
}