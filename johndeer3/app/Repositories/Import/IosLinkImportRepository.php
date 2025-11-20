<?php

namespace App\Repositories\Import;

use App\Models\Import\IosLinkImport;

class IosLinkImportRepository
{
    public function getLastRow(): ?IosLinkImport
    {
        return IosLinkImport::latest('id')->first();
    }
}
