<?php

namespace App\Models\Catalogs\Calc;

use App\Casts\MoneyCast;
use App\Helpers\ConvertNumber;
use App\Models\BaseModel;
use App\Services\Calc\CompositeItemCalcInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 * @property string $type
 * @property string $article
 * @property string $name
 * @property integer $price
 * @property integer $discount_price
 * @property integer $qty
 * @property integer $group_id
 *
 */
class Spares extends BaseModel implements CompositeItemCalcInterface
{
    const TYPE_VOLVO = 'volvo';
    const TYPE_MITSUBISHI = 'mitsubishi';
    const TYPE_RENAULT = 'renault';

    const DEFAULT_QTY = 1;

    public const TABLE = 'spares';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $casts = [
        'active' => 'bool',
        'price' => MoneyCast::class,
        'discount_price' => MoneyCast::class,
    ];

    public static function listType(): array
    {
        return [
            self::TYPE_VOLVO => self::TYPE_VOLVO,
            self::TYPE_MITSUBISHI => self::TYPE_MITSUBISHI,
            self::TYPE_RENAULT => self::TYPE_RENAULT,
        ];
    }

    public static function checkType($type): bool
    {
        return array_key_exists($type, self::listType());
    }

    public static function assetType($type): void
    {
        if(!self::checkType($type)){
            throw new \InvalidArgumentException(__('error.not valid car type for spares', ['type' => $type]));
        }
    }

    // relations

    public function group(): BelongsTo
    {
        return $this->belongsTo(SparesGroup::class,'group_id', 'id');
    }

    // методы интерфейса для калькуляции ТО

    public function calcPrice(null|float $price = null): float
    {
        return prettyPrice((float)$this->price->getValue() * $this->qty());
    }

    public function calcPriceDiscount(null|float $price = null): null|float
    {
       return null != $this->discount_price ? prettyPrice((float)$this->discount_price->getValue() * $this->qty()) : null;
    }

    public function name(): string
    {
        return $this->group->current->name ?? $this->name;
    }

    public function qty(): string
    {
        return ConvertNumber::fromNumberToFloat($this->pivot->qty);
    }

    public function unit(): string
    {
        return (null != $this->group) ? $this->group->getUnitName() : '';
    }

    // scopes

    public function scopeArticleSearch(EloquentBuilder $query, string $search): EloquentBuilder
    {
        return $query->where('article','like', $search . '%');
    }
}
