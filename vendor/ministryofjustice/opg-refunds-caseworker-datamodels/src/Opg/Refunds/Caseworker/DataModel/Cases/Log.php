<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use DateTime;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

class Log extends AbstractDataModel
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var DateTime
     */
    protected $createdDateTime;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $userName;

    /**
     * @var int
     */
    protected $poaId;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): Log
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedDateTime(): DateTime
    {
        return $this->createdDateTime;
    }

    /**
     * @param DateTime $createdDateTime
     * @return $this
     */
    public function setCreatedDateTime(DateTime $createdDateTime): Log
    {
        $this->createdDateTime = $createdDateTime;

        return $this;
    }

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
    public function setTitle(string $title): Log
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Log
     */
    public function setMessage(string $message): Log
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId): Log
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return $this
     */
    public function setUserName(string $userName): Log
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return int
     */
    public function getPoaId(): int
    {
        return $this->poaId;
    }

    /**
     * @param int $poaId
     * @return $this
     */
    public function setPoaId(int $poaId): Log
    {
        $this->poaId = $poaId;

        return $this;
    }

    /**
     * Map properties to correct types
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    protected function map($property, $value)
    {
        switch ($property) {
            case 'createdDateTime':
                return (($value instanceof DateTime || is_null($value)) ? $value : new DateTime($value));
            default:
                return parent::map($property, $value);
        }
    }
}