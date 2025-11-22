<?php

namespace App\Traits\Technician;

use App\Models\Technicians\Technician;
use Illuminate\Support\Facades\Auth;

trait IsTechnician
{
    private function isCertifiedTechnician(): bool
    {
        $user = Auth::guard(Technician::GUARD)->user();
        return  $user && $user instanceof Technician && $user->isCommercialCertification();
    }
}
