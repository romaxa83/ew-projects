<?php

namespace App\Repositories;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function getBy(
        $field,
        $value,
        array $relations = [],
        $withException = false,
        $exceptionMessage = 'Model not found'
    ): null|BaseModel|Model;
}
