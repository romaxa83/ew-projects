<?php

namespace App\Contracts\Utilities;

use App\Models\BaseAuthenticatable;
use App\Models\BaseModel;

interface MorphModel
{
    public function getModel($type, $id): BaseModel|BaseAuthenticatable;
}
