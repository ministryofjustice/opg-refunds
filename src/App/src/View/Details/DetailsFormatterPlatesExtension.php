<?php

namespace App\View\Details;

use Opg\Refunds\Caseworker\DataModel\Applications\Application as ApplicationModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use InvalidArgumentException;
use Opg\Refunds\Caseworker\DataModel\RejectionReasonsFormatter;

/**
 * Class DetailsFormatterPlatesExtension
 * @package App\View\Details
 */
class DetailsFormatterPlatesExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('getApplicantName', [$this, 'getApplicantName']);
        $engine->registerFunction('getPaymentDetailsUsedText', [$this, 'getPaymentDetailsUsedText']);
        $engine->registerFunction('shouldShowPaymentDetailsUsedCountWarning', [$this, 'shouldShowPaymentDetailsUsedCountWarning']);
        $engine->registerFunction('getRejectionReasonsText', [$this, 'getRejectionReasonsText']);
        $engine->registerFunction('getPercentage', [$this, 'getPercentage']);
        $engine->registerFunction('getValueWithPercentage', [$this, 'getValueWithPercentage']);
        $engine->registerFunction('getOutcomeEmailDescription', [$this, 'getOutcomeEmailDescription']);
        $engine->registerFunction('getOutcomeTextDescription', [$this, 'getOutcomeTextDescription']);
    }

    public function getApplicantName(ApplicationModel $application)
    {
        if ($application->getApplicant() === 'donor') {
            return "{$application->getDonor()->getCurrent()->getName()->getFormattedName()} (Donor)";
        } elseif ($application->getApplicant() === 'attorney') {
            return "{$application->getAttorney()->getCurrent()->getName()->getFormattedName()} (Attorney)";
        }

        return '';
    }

    public function getPaymentDetailsUsedText($accountHashCount)
    {
        if ($accountHashCount === null) {
            throw new InvalidArgumentException('Account hash count must be set');
        }

        if ($accountHashCount < 1) {
            throw new InvalidArgumentException('Account hash count is set to an invalid value: ' . $accountHashCount);
        } elseif ($accountHashCount === 1) {
            return "Used once";
        } elseif ($accountHashCount === 2) {
            return "Used twice";
        }

        return "Used {$accountHashCount} times";
    }

    public function shouldShowPaymentDetailsUsedCountWarning($accountHashCount)
    {
        return $accountHashCount > 1;
    }

    public function getRejectionReasonsText(string $rejectionReason)
    {
        return RejectionReasonsFormatter::getRejectionReasonText($rejectionReason);
    }

    public function getPercentage(int $total, int $value)
    {
        if ($total === 0) {
            return '0.00%';
        }

        $percentage = ($value / $total) * 100;

        return sprintf("%.2f%%", $percentage);
    }

    public function getValueWithPercentage(int $total, int $value)
    {
        return "{$value} ({$this->getPercentage($total, $value)})";
    }

    public function getOutcomeEmailDescription(ClaimModel $claim)
    {
        $notes = [];

        if ($claim->getStatus() === ClaimModel::STATUS_DUPLICATE) {
            $notes = $claim->getNotesOfType(NoteModel::TYPE_CLAIM_DUPLICATE_EMAIL_SENT);
        } elseif ($claim->getStatus() === ClaimModel::STATUS_REJECTED) {
            $notes = $claim->getNotesOfType(NoteModel::TYPE_CLAIM_REJECTED_EMAIL_SENT);
        } elseif ($claim->getStatus() === ClaimModel::STATUS_ACCEPTED) {
            $notes = $claim->getNotesOfType(NoteModel::TYPE_CLAIM_ACCEPTED_EMAIL_SENT);
        }

        $notesDescription = [];

        foreach ($notes as $note) {
            $notesDescription[] = 'Email sent on ' . date('d/m/Y', $note->getCreatedDateTime()->getTimestamp());
        }

        return join('. ', $notesDescription);
    }

    public function getOutcomeTextDescription(ClaimModel $claim)
    {
        $notes = [];

        if ($claim->getStatus() === ClaimModel::STATUS_DUPLICATE) {
            $notes = $claim->getNotesOfType(NoteModel::TYPE_CLAIM_DUPLICATE_TEXT_SENT);
        } elseif ($claim->getStatus() === ClaimModel::STATUS_REJECTED) {
            $notes = $claim->getNotesOfType(NoteModel::TYPE_CLAIM_REJECTED_TEXT_SENT);
        } elseif ($claim->getStatus() === ClaimModel::STATUS_ACCEPTED) {
            $notes = $claim->getNotesOfType(NoteModel::TYPE_CLAIM_ACCEPTED_TEXT_SENT);
        }

        $notesDescription = [];

        foreach ($notes as $note) {
            $notesDescription[] = 'Text sent on ' . date('d/m/Y', $note->getCreatedDateTime()->getTimestamp());
        }

        return join('. ', $notesDescription);
    }
}
