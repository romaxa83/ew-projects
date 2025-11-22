<?php

namespace App\Events\Commercial;

use App\Models\Commercial\CommercialProject;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendCommercialProjectToOnec
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected CommercialProject $model)
    {}

    public function getCommercialProject(): CommercialProject
    {
        return $this->model;
    }
}

