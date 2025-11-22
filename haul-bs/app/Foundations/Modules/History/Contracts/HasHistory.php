<?php

namespace App\Foundations\Modules\History\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasHistory
{
    public function histories(): MorphMany;

    public function dataForUpdateHistory(): array;
}
