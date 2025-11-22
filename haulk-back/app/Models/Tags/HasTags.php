<?php

namespace App\Models\Tags;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface HasTags
{
    public function tags(): MorphToMany;
}
