<?php

namespace App\View\Search;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

/**
 * Class SearchPlatesExtension
 * @package App\View\Search
 */
class SearchPlatesExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('getOrderByParameters', [$this, 'getOrderByParameters']);
        $engine->registerFunction('getCurrentSort', [$this, 'getCurrentSort']);
    }

    public function getOrderByParameters($searchParameters, string $orderBy)
    {
        $orderByParameters = ['orderBy' => $orderBy, 'sort' => 'asc'];

        if (isset($searchParameters['orderBy']) && $searchParameters['orderBy'] === $orderBy
            && isset($searchParameters['sort']) && $searchParameters['sort'] === 'asc') {
            $orderByParameters['sort'] = 'desc';
        }

        return $orderByParameters;
    }

    public function getCurrentSort($searchParameters, string $orderBy)
    {
        $sort = false;

        if (isset($searchParameters['orderBy']) && $searchParameters['orderBy'] === $orderBy && isset($searchParameters['sort'])) {
            $sort = $searchParameters['sort'];
        }

        return $sort;
    }
}