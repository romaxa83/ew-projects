<?php

namespace App\Dto\SupportRequests;

use App\Dto\BaseDictionaryDto;
use App\Models\Support\RequestSubjects\SupportRequestSubject;

class SupportRequestSubjectDto extends BaseDictionaryDto
{

    protected function getDefaultActive(): bool
    {
        return SupportRequestSubject::DEFAULT_ACTIVE;
    }
}

