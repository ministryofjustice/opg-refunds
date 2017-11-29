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
        $engine->registerFunction('getPoaCaseNumbersText', [$this, 'getPoaCaseNumbersText']);
    }

    public function isSearchParametersSet($searchParameters)
    {
        return isset($searchParameters['search']) || isset($searchParameters['status']) || isset($searchParameters['assignedToId']) || isset($searchParameters['poaCaseNumbers']);
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

    public function getPoaCaseNumbersText($searchParameters)
    {
        if (!isset($searchParameters['poaCaseNumbers'])) {
            return '';
        }

        $poaCaseNumbers = $searchParameters['poaCaseNumbers'];

        if (empty($poaCaseNumbers)) {
            return '';
        }

        $poaCaseNumbersArray = explode(',', $poaCaseNumbers);

        for ($i = 0; $i < count($poaCaseNumbersArray); $i++) {
            if (strlen($poaCaseNumbersArray[$i]) === 12) {
                $poaCaseNumbersArray[$i] = join('-', str_split($poaCaseNumbersArray[$i], 4));
            }
        }

        return ' using POA case number' . (count($poaCaseNumbersArray) > 1 ? 's ' : ' ') . join(', ', $poaCaseNumbersArray);
    }
}