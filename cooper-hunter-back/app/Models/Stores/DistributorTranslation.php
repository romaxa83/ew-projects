<?php

namespace App\Models\Stores;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Stores\DistributorTranslationFactory;

/**
 * @method static DistributorTranslationFactory factory()
 */
class DistributorTranslation extends BaseModel
{
    use HasFactory;

    public const TABLE = 'distributor_translations';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'language',
    ];
}
