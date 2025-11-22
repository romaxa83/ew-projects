<?php

namespace App\Models\Catalog\Certificates;

use App\Filters\Catalog\Certificates\CertificateFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Certificates\CertificateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int id
 * @property int certificate_type_id
 * @property string number
 * @property string|null link
 *
 * @see Certificate::type()
 * @property-read CertificateType type
 *
 * @see Certificate::scopeJoinType()
 * @method Builder|static joinType(string $type = 'inner')
 *
 * @see Certificate::scopeAddTypeName()
 * @method Builder|static addTypeName(string $as = 'type_name')
 * @property string type_name
 *
 * @method static CertificateFactory factory(...$parameters)
 */
class Certificate extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'certificates';

    public $timestamps = false;

    protected $table = self::TABLE;

    public function modelFilter(): string
    {
        return CertificateFilter::class;
    }

    public function type(): BelongsTo|CertificateType
    {
        return $this->belongsTo(CertificateType::class, 'certificate_type_id');
    }

    public function scopeJoinType(Builder|self $b, string $type = 'inner'): void
    {
        $b->join(
            CertificateType::TABLE,
            self::TABLE . '.certificate_type_id',
            '=',
            CertificateType::TABLE . '.id',
            $type
        );
    }

    public function scopeAddTypeName(Builder|self $b, string $as = 'type_name'): void
    {
        $b->joinType()
            ->addSelect(
                DB::raw(
                    sprintf(
                        '%s as %s',
                        CertificateType::TABLE . '.type',
                        $as
                    )
                )
            );
    }
}
