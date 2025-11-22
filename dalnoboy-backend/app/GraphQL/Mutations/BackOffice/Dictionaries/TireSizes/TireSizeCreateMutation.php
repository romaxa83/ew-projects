<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes;

use App\GraphQL\Mutations\Common\Dictionaries\TireSizes\BaseTireSizeCreateMutation;

class TireSizeCreateMutation extends BaseTireSizeCreateMutation
{
    protected function setGuard(): void
    {
        $this->setAdminGuard();
    }
}
