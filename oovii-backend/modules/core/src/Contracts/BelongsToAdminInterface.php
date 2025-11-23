<?php

namespace WezomCms\Core\Contracts;

use Illuminate\Database\Eloquent\Relations\Relation;

interface BelongsToAdminInterface
{
    public function administrator(): Relation;
}
