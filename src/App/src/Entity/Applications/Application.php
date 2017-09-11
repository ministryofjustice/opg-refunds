<?php

namespace App\Entity\Applications;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/*CREATE INDEX IF NOT EXISTS created_at ON :APPLICATION_TABLE_NAME USING btree (created);
CREATE INDEX IF NOT EXISTS to_process ON :APPLICATION_TABLE_NAME USING btree (processed,created);*/

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
     * @var array
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
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}