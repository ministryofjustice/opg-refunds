<?php

namespace Applications\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="application", indexes={@ORM\Index(name="created_at", columns={"created"}), @ORM\Index(name="to_process", columns={"processed", "created"})})
 **/
class Application
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="bigint")
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(type="datetimetz")
     */
    protected $created;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $processed;

    /**
     * @var resource|string
     * @ORM\Column(type="binary", nullable=true)
     */
    protected $data;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * @param bool $processed
     */
    public function setProcessed(bool $processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        if (is_resource($this->data)) {
            $this->data = stream_get_contents($this->data);
        }

        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}