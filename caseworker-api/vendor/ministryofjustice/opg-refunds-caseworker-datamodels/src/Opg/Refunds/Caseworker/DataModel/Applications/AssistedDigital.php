<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

class AssistedDigital extends AbstractDataModel
{
    const TYPE_DONOR_DECEASED = "donor_deceased";
    const TYPE_ASSISTED_DIGITAL = "assisted_digital";
    const TYPE_REFUSE_CLAIM_ONLINE = "refuse_claim_online";
    const TYPE_DEPUTY = "deputy";
    const TYPE_CHEQUE = "cheque";

    /**
     * @var string
     */
    protected $notes;

    /**
     * @var array
     */
    protected $meta;

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return $this
     */
    public function setNotes(string $notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     * @return $this
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }
}