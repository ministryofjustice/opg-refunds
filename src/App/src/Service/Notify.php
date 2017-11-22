<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Service\Claim as ClaimService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class Notify
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var NotifyClient
     */
    private $notifyClient;

    /**
     * @var Claim
     */
    private $claimService;

    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManager $entityManager, NotifyClient $notifyClient, ClaimService $claimService)
    {
        $this->entityManager = $entityManager;
        $this->notifyClient = $notifyClient;
        $this->claimService = $claimService;
        $this->repository = $entityManager->getRepository(ClaimEntity::class);
    }

    public function notifyAll()
    {
        $notified = [];

        return $notified;
    }
}