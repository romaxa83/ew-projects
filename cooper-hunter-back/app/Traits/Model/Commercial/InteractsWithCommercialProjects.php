<?php

namespace App\Traits\Model\Commercial;

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CredentialsRequest;
use App\Models\Commercial\RDPAccount;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait InteractsWithCommercialProjects
{
    public function commercialProjects(): MorphMany|CommercialProject
    {
        return $this->morphMany(CommercialProject::class, 'member');
    }

    public function credentialRequests(): MorphMany|CredentialsRequest
    {
        return $this->morphMany(CredentialsRequest::class, 'member');
    }

    public function rdpAccount(): MorphOne|RDPAccount
    {
        return $this->morphOne(RDPAccount::class, 'member');
    }

    public function hasValidRdpAccount(): bool
    {
        return $this->rdpAccount && $this->rdpAccount->isValid();
    }
}
