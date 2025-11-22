<?php

namespace App\Contracts\Members;

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CredentialsRequest;
use App\Models\Commercial\RDPAccount;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasCommercialProjects extends Member
{
    public function commercialProjects(): MorphMany|CommercialProject;

    public function rdpAccount(): MorphOne|RDPAccount;

    public function credentialRequests(): MorphMany|CredentialsRequest;

    public function hasValidRdpAccount(): bool;
}