<?php

namespace App\Traits\Model;

use App\Models\Dictionaries\TireSize;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTireSizesTrait
{
    public function tireSizes(): HasMany|TireSize
    {
        return $this->hasMany(TireSize::class);
    }
}
