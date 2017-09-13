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
     * @return Name $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
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
     * @return Name $this
     */
    public function setFirst(string $first)
    {
        $this->first = $first;
        return $this;
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
     * @return Name $this
     */
    public function setLast(string $last)
    {
        $this->last = $last;
        return $this;
    }
}
