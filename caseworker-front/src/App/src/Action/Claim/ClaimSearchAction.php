<?php

namespace App\Action\Claim;

use App\Form\ClaimSearch;
use App\Service\Claim\Claim as ClaimService;
use App\Service\User\User as UserService;
use App\Action\AbstractAction;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class ClaimSearchAction
 * @package App\Action\Claim
 */
class ClaimSearchAction extends AbstractAction
{
    /**
     * @var ClaimService
     */
    protected $claimService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * AbstractClaimAction constructor
     * @param ClaimService $claimService
     * @param UserService $userService
     */
    public function __construct(ClaimService $claimService, UserService $userService)
    {
        $this->claimService = $claimService;
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        $searchParameters = array_merge($request->getQueryParams(), $request->getParsedBody());

        $form->setData($searchParameters);

        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            //  Search
            if ($form->isValid()) {
                //  Unset non search form links so they aren't added to search links
                unset($searchParameters['secret']);
                unset($searchParameters['submit']);

                //  Unset page as this is a new search
                unset($searchParameters['page']);

                //  Unset blank search parameters
                if ($searchParameters['search'] === '') {
                    unset($searchParameters['search']);
                }
                if ($searchParameters['statuses'] === '') {
                    unset($searchParameters['statuses']);
                }
                if ($searchParameters['assignedToFinishedById'] === '') {
                    unset($searchParameters['assignedToFinishedById']);
                }

                //  Redirect to search with get params
                return $this->redirectToRoute('claim.search', [], $searchParameters);
            }
        }

        $claimSummaryPage = $this->claimService->searchClaims($searchParameters);

        //  Unset page so it isn't added to search links
        unset($searchParameters['page']);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-search-page', [
            'form'             => $form,
            'claimSummaryPage' => $claimSummaryPage,
            'searchParameters' => $searchParameters
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ClaimSearch
     */
    protected function getForm(ServerRequestInterface $request): ClaimSearch
    {
        $session = $request->getAttribute('session');

        $userSummaryPage = $this->userService->searchUsers(null, null, null, UserModel::STATUS_ACTIVE);
        $userSummaries = $userSummaryPage->getUserSummaries();

        $form = new ClaimSearch([
            'userSummaries' => $userSummaries,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
