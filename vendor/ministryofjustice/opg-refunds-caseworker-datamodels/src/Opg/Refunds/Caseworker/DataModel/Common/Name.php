<?php

namespace Opg\Refunds\Caseworker\DataModel\Common;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class Name
 * @package Opg\Refunds\Caseworker\DataModel\Common
 */
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
     * @return $this
     */
    public function setTitle(string $title): Name
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
     * @return $this
     */
    public function setFirst(string $first): Name
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
     * @return $this
     */
    public function setLast(string $last): Name
    {
        $this->last = $last;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormattedName(): string
    {
        return "{$this->getTitle()} {$this->getFirst()} {$this->getLast()}";
    }
}