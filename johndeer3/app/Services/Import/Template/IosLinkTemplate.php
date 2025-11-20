<?php

namespace App\Services\Import\Template;

use App\Models\User\IosLink;

class IosLinkTemplate extends TemplateAbstract
{
    protected $synonyms = [
        'Code' => 'code',
        'Code Redemption Link' => 'link',
    ];

    public static $requiredColumns = [
        'Code',
        'Code Redemption Link',
    ];

    public function setCodeAttribute($code): void
    {
        $iosLink = IosLink::where('code','=',$code)->first();
        $this->attributes['code'] = $iosLink ? null : $code;
    }

    public function isNotValid(): bool
    {
        if (!$this->code) {
            $this->message[] = "Invalid row for import in row = {$this->data['row_id']} , this row skipped for import";
            $this->message[] = "Invalid code - {$this->data['Code']};";
        }
        return !$this->code;
    }
}
