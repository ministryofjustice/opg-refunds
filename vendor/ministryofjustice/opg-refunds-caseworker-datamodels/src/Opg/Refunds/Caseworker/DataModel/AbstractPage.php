<?php

namespace Opg\Refunds\Caseworker\DataModel;

class AbstractPage extends AbstractDataModel
{
    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $pageSize;

    /**
     * @var int
     */
    protected $pageCount;

    /**
     * @var int
     */
    protected $total;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage(int $page): AbstractPage
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize(int $pageSize): AbstractPage
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    /**
     * @param int $pageCount
     * @return $this
     */
    public function setPageCount(int $pageCount): AbstractPage
    {
        $this->pageCount = $pageCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     * @return $this
     */
    public function setTotal(int $total): AbstractPage
    {
        $this->total = $total;

        return $this;
    }
}