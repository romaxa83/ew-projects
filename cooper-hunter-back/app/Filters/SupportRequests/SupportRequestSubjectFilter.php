<?php

namespace App\Filters\SupportRequests;

use App\Filters\BaseDictionaryModelFilter;
use App\Models\Support\RequestSubjects\SupportRequestSubject;

class SupportRequestSubjectFilter extends BaseDictionaryModelFilter
{
    public const TABLE = SupportRequestSubject::TABLE;
}
