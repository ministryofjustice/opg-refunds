<?php

namespace App\Action;

use App\Service\User as UserService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class UserSearchAction
 * @package App\Action
 */
class UserSearchAction extends AbstractRestfulAction
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * READ/GET index action
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $queryParameters = $request->getQueryParams();

        $page = isset($queryParameters['page']) ? $queryParameters['page'] : null;
        $pageSize = isset($queryParameters['pageSize']) ? $queryParameters['pageSize'] : null;
        $search = isset($queryParameters['search']) ? $queryParameters['search'] : null;
        $status = isset($queryParameters['status']) ? $queryParameters['status'] : null;
        $orderBy = isset($queryParameters['orderBy']) ? $queryParameters['orderBy'] : null;
        $sort = isset($queryParameters['sort']) ? $queryParameters['sort'] : null;

        //  Search users
        $userSummaryPage = $this->userService->search($page, $pageSize, $search, $status, $orderBy, $sort);

        return new JsonResponse($userSummaryPage->getArrayCopy());
    }
}
