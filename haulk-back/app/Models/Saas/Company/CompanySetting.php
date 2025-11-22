<?php

namespace App\Models\Saas\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CompanySetting extends Model
{
    use HasFactory;

    public const TABLE = 'company_settings';

    protected $fillable = [
        'company_id',
        'filebrowser_prefix'
    ];

    public $timestamps = false;

    public $incrementing = false;

    public $primaryKey = 'company_id';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getFileBrowserPrefix(): ?string
    {
        return $this->filebrowser_prefix;
    }
}
