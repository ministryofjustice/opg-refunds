<?php

namespace App\View\Note;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use Mezzio\Helper\UrlHelper;

/**
 * Class NoteFormatterPlatesExtension
 * @package App\View\Note
 */
class NoteFormatterPlatesExtension implements ExtensionInterface
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * NoteFormatterPlatesExtension constructor
     * @param UrlHelper $urlHelper
     */
    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('getTypeTitleText', [$this, 'getTypeTitleText']);
        $engine->registerFunction('getDuplicateLinkMessage', [$this, 'getDuplicateLinkMessage']);
    }

    public function getTypeTitleText(string $type)
    {
        switch ($type) {
            case NoteModel::TYPE_USER:
                return 'User note';
            case NoteModel::TYPE_CLAIM_SUBMITTED:
                return 'Claim submitted';
            case NoteModel::TYPE_ASSISTED_DIGITAL:
                return 'Phone claim';
            case NoteModel::TYPE_CLAIM_PENDING:
                return 'Claim returned';
            case NoteModel::TYPE_CLAIM_IN_PROGRESS:
                return 'Claim started by caseworker';
            case NoteModel::TYPE_CLAIM_DUPLICATE:
                return 'Duplicate claim';
            case NoteModel::TYPE_CLAIM_REJECTED:
                return 'Claim rejected';
            case NoteModel::TYPE_CLAIM_ACCEPTED:
                return 'Claim accepted';
            case NoteModel::TYPE_CLAIM_WITHDRAWN:
                return 'Claim withdrawn';
            case NoteModel::TYPE_POA_ADDED:
                return 'POA added';
            case NoteModel::TYPE_POA_EDITED:
                return 'POA edited';
            case NoteModel::TYPE_POA_DELETED:
                return 'POA deleted';
            case NoteModel::TYPE_POA_AUTOMATION_RAN:
                return 'Pre-population ran';
            case NoteModel::TYPE_POA_AUTOMATION_DONOR_MISMATCH:
                return 'Pre-population donor/case reference mismatch';
            case NoteModel::TYPE_POA_AUTOMATION_DUPLICATE:
                return 'Pre-population detected likely duplicate claim';
            case NoteModel::TYPE_NO_MERIS_POAS:
                return 'No Meris POAs';
            case NoteModel::TYPE_MERIS_POAS_FOUND:
                return 'Meris POA found';
            case NoteModel::TYPE_NO_SIRIUS_POAS:
                return 'No Sirius POAs';
            case NoteModel::TYPE_SIRIUS_POAS_FOUND:
                return 'Sirius POA found';
            case NoteModel::TYPE_CLAIM_DUPLICATE_EMAIL_SENT:
                return 'Email sent';
            case NoteModel::TYPE_CLAIM_DUPLICATE_TEXT_SENT:
                return 'Text sent';
            case NoteModel::TYPE_CLAIM_DUPLICATE_LETTER_SENT:
                return 'Letter sent';
            case NoteModel::TYPE_CLAIM_DUPLICATE_PHONE_CALLED:
                return 'Phone called';
            case NoteModel::TYPE_CLAIM_REJECTED_EMAIL_SENT:
                return 'Email sent';
            case NoteModel::TYPE_CLAIM_REJECTED_TEXT_SENT:
                return 'Text sent';
            case NoteModel::TYPE_CLAIM_REJECTED_LETTER_SENT:
                return 'Letter sent';
            case NoteModel::TYPE_CLAIM_REJECTED_PHONE_CALLED:
                return 'Phone called';
            case NoteModel::TYPE_CLAIM_ACCEPTED_EMAIL_SENT:
                return 'Email sent';
            case NoteModel::TYPE_CLAIM_ACCEPTED_TEXT_SENT:
                return 'Text sent';
            case NoteModel::TYPE_CLAIM_ACCEPTED_LETTER_SENT:
                return 'Letter sent';
            case NoteModel::TYPE_CLAIM_ACCEPTED_PHONE_CALLED:
                return 'Phone called';
            case NoteModel::TYPE_REFUND_ADDED:
                return 'Refund added';
            case NoteModel::TYPE_REFUND_UPDATED:
                return 'Refund updated';
            case NoteModel::TYPE_REFUND_DOWNLOADED:
                return 'Refund downloaded';
            case NoteModel::TYPE_CLAIM_OUTCOME_CHANGED:
                return 'Claim outcome changed';
            case NoteModel::TYPE_CLAIM_ASSIGNED:
                return 'Claim assigned';
            case NoteModel::TYPE_CLAIM_REASSIGNED:
                return 'Claim reassigned';
            case NoteModel::TYPE_CLAIM_CONTACT_DETAILS_UPDATED:
                return 'Contact details updated';
        }

        return $type;
    }

    public function getDuplicateLinkMessage(string $message)
    {
        //Replace case references with link to search page
        $message = preg_replace_callback(
            '/(\d{4}-\d{4}-\d{4}|\d{7}\/\d{1,2})/',
            function ($matches) {
                return '<a href=' . $this->urlHelper->generate(
                    'claim.search',
                    [],
                    ['orderBy' => 'received', 'sort' => 'desc', 'poaCaseNumbers' => str_replace('-', '', $matches[0])]
                ) . ' target="_blank">' . $matches[0] . '</a>';
            },
            $message
        );

        //Replace claim codes with link to claim
        $message = preg_replace_callback(
            '/(R\d{0,3} \d{0,4} \d{0,4})/',
            function ($matches) {
                return '<a href=' . $this->urlHelper->generate(
                    'claim',
                    ['id' => IdentFormatter::parseId($matches[0])]
                ) . ' target="_blank">' . $matches[0] . '</a>';
            },
            $message
        );

        return $message;
    }
}
