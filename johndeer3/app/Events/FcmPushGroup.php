<?php

namespace App\Events;

use App\Models\Report\Report;
use Illuminate\Queue\SerializesModels;

class FcmPushGroup
{
    use SerializesModels;

    public $report;
    public $templateName;

    public function __construct(Report $report, string $templateName)
    {
        $this->report = $report;
        $this->templateName = $templateName;
    }
}

