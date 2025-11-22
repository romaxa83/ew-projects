<?php

namespace App\Rules\ExistsRules;

use App\Models\BaseModel;
use App\Models\Projects\System;
use App\Rules\BaseBelongsToMemberRule;

class ProjectSystemExistsRule extends BaseBelongsToMemberRule
{
    protected string $through = 'project';

    protected string|BaseModel $model = System::class;
}
