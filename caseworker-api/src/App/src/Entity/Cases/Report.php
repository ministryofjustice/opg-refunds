<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="report", indexes={
 * @ORM\Index(name="idx_report_type", columns={"type"}),
 * @ORM\Index(name="idx_report_type_title", columns={"type", "title"}),
 * @ORM\Index(name="idx_report_type_start_datetime_end_datetime", columns={"type", "start_datetime", "end_datetime"})
 * })
 **/
class Report extends AbstractEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @var DateTime
     * @ORM\Column(name="start_datetime", type="datetimetz")
     */
    protected $startDateTime;

    /**
     * @var DateTime
     * @ORM\Column(name="end_datetime", type="datetimetz")
     */
    protected $endDateTime;

    /**
     * @var array
     * @ORM\Column(type="json_array", options={"jsonb"=true})
     */
    protected $data;

    /**
     * @var DateTime
     * @ORM\Column(name="generated_datetime", type="datetimetz")
     */
    protected $generatedDateTime;

    /**
     * @var int
     * @ORM\Column(name="generation_time_ms", type="integer")
     */
    protected $generationTimeInMs;

    public function __construct(
        string $type,
        string $title,
        DateTime $startDateTime,
        DateTime $endDateTime,
        array $data,
        int $generationTimeInMs
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->data = $data;
        $this->generationTimeInMs = $generationTimeInMs;

        $this->generatedDateTime = new DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
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
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return DateTime
     */
    public function getStartDateTime(): DateTime
    {
        return $this->startDateTime;
    }

    /**
     * @param DateTime $startDateTime
     */
    public function setStartDateTime(DateTime $startDateTime)
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * @return DateTime
     */
    public function getEndDateTime(): DateTime
    {
        return $this->endDateTime;
    }

    /**
     * @param DateTime $endDateTime
     */
    public function setEndDateTime(DateTime $endDateTime)
    {
        $this->endDateTime = $endDateTime;
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

    /**
     * @return DateTime
     */
    public function getGeneratedDateTime(): DateTime
    {
        return $this->generatedDateTime;
    }

    /**
     * @param DateTime $generatedDateTime
     */
    public function setGeneratedDateTime(DateTime $generatedDateTime)
    {
        $this->generatedDateTime = $generatedDateTime;
    }

    /**
     * @return int
     */
    public function getGenerationTimeInMs(): int
    {
        return $this->generationTimeInMs;
    }

    /**
     * @param int $generationTimeInMs
     */
    public function setGenerationTimeInMs(int $generationTimeInMs)
    {
        $this->generationTimeInMs = $generationTimeInMs;
    }
}
