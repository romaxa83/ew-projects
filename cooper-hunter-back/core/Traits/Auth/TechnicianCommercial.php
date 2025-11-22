<?php

namespace Core\Traits\Auth;

use App\Models\Technicians\Technician;
use Core\Exceptions\TranslatedException;

trait TechnicianCommercial
{
    protected function isTechnicianCommercial(): void
    {
        if($this->user() instanceof Technician && !$this->user()->isCommercialCertification()){
            throw new TranslatedException(__("exceptions.commercial.technician does\'n have a commercial certificate"), 502);
        }
    }
}

