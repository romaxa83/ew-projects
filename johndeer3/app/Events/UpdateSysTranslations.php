<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UpdateSysTranslations
{
    use SerializesModels;

    public $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }
}
