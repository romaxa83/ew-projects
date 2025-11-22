<?php

namespace App\Models\Catalog\Certificates;

use App\Filters\Catalog\Certificates\TypeFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Certificates\CertificateTypeFactory;

/**
 * @property int id
 * @property string type
 *
 * @method static CertificateTypeFactory factory(...$parameters)
 */
class CertificateType extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'certificate_types';

    public $timestamps = false;

    protected $table = self::TABLE;

    public function modelFilter(): string
    {
        return TypeFilter::class;
    }
}
