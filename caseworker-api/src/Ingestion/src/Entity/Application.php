<?php

namespace Ingestion\Entity;

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
     * IMPORTANT - $this->data is set as a PHP "resource" by Doctrine but leaving it like that means that
     * repeated calls to this function will yield different results (i.e. the first call will return the full
     * string and subsequent calls will return a blank string). Therefore on the first call the resource is set
     * to a proper string value.
     *
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
