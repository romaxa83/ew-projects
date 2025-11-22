<?php

namespace App\Events\BS\Vehicles;

use App\Models\Saas\Company\Company;
use Illuminate\Queue\SerializesModels;

class ToggleUseBSEvent
{
    use SerializesModels;

    public Company $company;
    public $oldValue;

    public function __construct(
        Company $company,
        bool $oldValue
    )
    {
        $this->company = $company;
        $this->oldValue = $oldValue;
    }
}
