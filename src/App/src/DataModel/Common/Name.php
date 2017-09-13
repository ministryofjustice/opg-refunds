<?php

namespace App\DataModel\Common;

use App\DataModel\AbstractDataModel;

class Name extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $first;

    /**
     * @var string
     */
    protected $last;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getFirst(): string
    {
        return $this->first;
    }

    /**
     * @param string $first
     */
    public function setFirst(string $first)
    {
        $this->first = $first;
    }

    /**
     * @return string
     */
    public function getLast(): string
    {
        return $this->last;
    }

    /**
     * @param string $last
     */
    public function setLast(string $last)
    {
        $this->last = $last;
    }
}