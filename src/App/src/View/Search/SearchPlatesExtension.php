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
        $engine->registerFunction('isSearchParametersSet', [$this, 'isSearchParametersSet']);
        $engine->registerFunction('getOrderByParameters', [$this, 'getOrderByParameters']);
        $engine->registerFunction('getCurrentSort', [$this, 'getCurrentSort']);
    }

    public function isSearchParametersSet($searchParameters)
    {
        return isset($searchParameters['search']) || isset($searchParameters['status']) || isset($searchParameters['assignedToId']);
    }

    public function getOrderByParameters($searchParameters, string $orderBy)
    {
        $orderByParameters = ['orderBy' => $orderBy, 'sort' => 'asc'];

        switch ($orderBy) {
            case 'received':
            case 'finished':
                $orderByParameters['sort'] = 'desc';
        }

        if (isset($searchParameters['orderBy']) && $searchParameters['orderBy'] === $orderBy && isset($searchParameters['sort'])) {
            if ($searchParameters['sort'] === 'asc') {
                $orderByParameters['sort'] = 'desc';
            } elseif ($searchParameters['sort'] === 'desc') {
                $orderByParameters['sort'] = 'asc';
            }
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