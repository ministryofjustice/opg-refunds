<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use DateTime;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

class Note extends AbstractDataModel
{
    const TYPE_USER = 'user_note';
    const TYPE_CLAIM_SUBMITTED = 'claim_submitted';
    const TYPE_ASSISTED_DIGITAL = 'assisted_digital';
    const TYPE_CLAIM_PENDING = 'claim_pending';
    const TYPE_CLAIM_IN_PROGRESS = 'claim_in_progress';
    const TYPE_CLAIM_DUPLICATE = 'claim_duplicate';
    const TYPE_CLAIM_REJECTED = 'claim_rejected';
    const TYPE_CLAIM_ACCEPTED = 'claim_accepted';
    const TYPE_POA_ADDED = 'poa_added';
    const TYPE_POA_EDITED = 'poa_edited';
    const TYPE_POA_DELETED = 'poa_deleted';
    const TYPE_NO_MERIS_POAS = 'no_meris_poas';
    const TYPE_MERIS_POAS_FOUND = 'meris_poas_found';
    const TYPE_NO_SIRIUS_POAS = 'no_sirius_poas';
    const TYPE_SIRIUS_POAS_FOUND = 'sirius_poas_found';
    const TYPE_CLAIM_DUPLICATE_EMAIL_SENT = 'claim_duplicate_email_sent';
    const TYPE_CLAIM_DUPLICATE_TEXT_SENT = 'claim_duplicate_text_sent';
    const TYPE_CLAIM_DUPLICATE_LETTER_SENT = 'claim_duplicate_letter_sent';
    const TYPE_CLAIM_DUPLICATE_PHONE_CALLED = 'claim_duplicate_phone_called';
    const TYPE_CLAIM_REJECTED_EMAIL_SENT = 'claim_rejected_email_sent';
    const TYPE_CLAIM_REJECTED_TEXT_SENT = 'claim_rejected_text_sent';
    const TYPE_CLAIM_REJECTED_LETTER_SENT = 'claim_rejected_letter_sent';
    const TYPE_CLAIM_REJECTED_PHONE_CALLED = 'claim_rejected_phone_called';
    const TYPE_CLAIM_ACCEPTED_EMAIL_SENT = 'claim_accepted_email_sent';
    const TYPE_CLAIM_ACCEPTED_TEXT_SENT = 'claim_accepted_text_sent';
    const TYPE_CLAIM_ACCEPTED_LETTER_SENT = 'claim_accepted_letter_sent';
    const TYPE_CLAIM_ACCEPTED_PHONE_CALLED = 'claim_accepted_phone_called';
    const TYPE_REFUND_ADDED = 'refund_added';
    const TYPE_REFUND_UPDATED = 'refund_updated';
    const TYPE_REFUND_DOWNLOADED = 'refund_downloaded';
    const TYPE_CLAIM_OUTCOME_CHANGED = 'claim_outcome_changed';
    const TYPE_CLAIM_ASSIGNED = 'claim_assigned';
    const TYPE_CLAIM_REASSIGNED = 'claim_reassigned';

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
    protected $type;

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
    public function setId(int $id): Note
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
    public function setCreatedDateTime(DateTime $createdDateTime): Note
    {
        $this->createdDateTime = $createdDateTime;

        return $this;
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
     * @return $this
     */
    public function setType(string $type): Note
    {
        $this->type = $type;

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
     * @return Note
     */
    public function setMessage(string $message): Note
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
    public function setUserId(int $userId): Note
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
    public function setUserName(string $userName): Note
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
    public function setPoaId(int $poaId): Note
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