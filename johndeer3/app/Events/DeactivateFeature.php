<?php

namespace App\Events;

use App\Models\Report\Feature\Feature;
use Illuminate\Queue\SerializesModels;

class DeactivateFeature
{
    use SerializesModels;

    public $feature;

    public function __construct(Feature $feature)
    {
        $this->feature = $feature;
    }
}
