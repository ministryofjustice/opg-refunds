<?php

namespace App\Action;

use App\Entity\Cases\User;
use App\Entity\Cases\Claim;
use App\Entity\Sirius\Poa as SiriusPoa;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PingAction
 * @package App\Action
 */
class PingAction extends AbstractRestfulAction
{
    /**
     * @var EntityManager
     */
    private $claimsEntityManager;

    /**
     * @var EntityManager
     */
    private $siriusEntityManager;

    /**
     * PingAction constructor
     *
     * @param EntityManager $claimsEntityManager
     * @param EntityManager $siriusEntityManager
     */
    public function __construct(EntityManager $claimsEntityManager, EntityManager $siriusEntityManager)
    {
        $this->claimsEntityManager = $claimsEntityManager;
        $this->siriusEntityManager = $siriusEntityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $claimDbConnectionSuccessful = false;
        $foundUser = false;
        try {
            $userRepository = $this->claimsEntityManager->getRepository(User::class);
            $users = $userRepository->findBy([], null, 1);
            $claimDbConnectionSuccessful = true;
            foreach ($users as $user) {
                /** @var User $user */
                $foundUser = $user->getId() > 0;
            }
        } catch (Exception $ex) {
            $claimDbConnectionSuccessful = $ex->getMessage();
        }

        $foundClaim = false;
        try {
            $claimRepository = $this->claimsEntityManager->getRepository(Claim::class);
            $claims = $claimRepository->findBy([], null, 1);
            $claimDbConnectionSuccessful = true;
            foreach ($claims as $claim) {
                /** @var Claim $claim */
                $foundClaim = $claim->getId() > 0;
            }
        } catch (Exception $ex) {
            $claimDbConnectionSuccessful = $ex->getMessage();
        }

        $siriusDbConnectionSuccessful = false;
        $foundSiriusPoa = false;
        try {
            $poaRepository = $this->siriusEntityManager->getRepository(SiriusPoa::class);
            $poas = $poaRepository->findBy([], null, 1);
            $siriusDbConnectionSuccessful = true;
            foreach ($poas as $poa) {
                /** @var SiriusPoa $poa */
                $foundSiriusPoa = $poa->getId() > 0;
            }
        } catch (Exception $ex) {
            $siriusDbConnectionSuccessful = $ex->getMessage();
        }

        return new JsonResponse([
            'ack' => time(),
            'claimDbConnectionSuccessful' => $claimDbConnectionSuccessful,
            'foundUser' => $foundUser,
            'foundClaim' => $foundClaim,
            'siriusDbConnectionSuccessful' => $siriusDbConnectionSuccessful,
            'foundSiriusPoa' => $foundSiriusPoa
        ]);
    }
}
