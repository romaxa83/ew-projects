<?php

namespace App\Foundations\Modules\Comment\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasComments
{
    public function comments(): MorphMany;
}
