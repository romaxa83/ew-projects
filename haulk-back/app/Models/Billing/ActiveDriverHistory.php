<?php

namespace App\Models\Billing;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveDriverHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());
    }
}
