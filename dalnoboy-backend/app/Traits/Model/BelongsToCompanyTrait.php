<?php

declare(strict_types=1);

namespace App\Traits\Model;

use App\Models\Companies\Company;
use App\Traits\Eloquent\WhereCompanyTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @see BelongsToCompanyTrait::company()
 * @property-read Company $company
 */
trait BelongsToCompanyTrait
{
    use WhereCompanyTrait;

    protected bool $hasCompanyForeignKey = true;

    public function company(): BelongsTo|Company
    {
        return $this->belongsTo(Company::class);
    }
}
