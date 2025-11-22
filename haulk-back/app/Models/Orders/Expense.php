<?php

namespace App\Models\Orders;

use App\Collections\Models\Orders\ExpenseCollection;
use App\Collections\Models\Orders\MediaCollection;
use App\Models\DiffableInterface;
use App\Models\Files\ExpenseImage;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use App\Traits\Diffable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $type_id
 * @property float $price
 * @property int $date
 * @property string $to
 */
class Expense extends Model implements HasMedia, DiffableInterface
{
    use HasMediaTrait;
    use Diffable;
    use HasFactory;

    public const TABLE_NAME = 'expenses';
    public const RECEIPT_FIELD_NAME = 'receipt_file';

    public const EXPENSE_COLLECTION_NAME = 'expenses';

    public const EXPENSE_TYPES = [
        1 => 'Pulling Fee',
        2 => 'Storage Fee',
        3 => 'Late Fee',
        4 => 'Dry Run Fee',
        5 => 'Tax',
        6 => 'Fuel',
        7 => 'Toll',
        8 => 'Other',
    ];

    protected $fillable = [
        'type_id',
        'price',
        'date',
        'to',
    ];

    protected $casts = [
        'price' => 'float'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function getImageClass(): string
    {
        return ExpenseImage::class;
    }

    public static function getTypesList(): array
    {
        $data = [];

        foreach (self::EXPENSE_TYPES as $k => $v) {
            $data[] = [
                'id' => $k,
                'title' => $v,
            ];
        }

        return $data;
    }

    public function getTypeNameAttribute(): string
    {
        return self::EXPENSE_TYPES[$this->type_id] ?? '';
    }

    public function newCollection(array $models = []): ExpenseCollection
    {
        return ExpenseCollection::make($models);
    }

    /**
     * @return Diffable[]
     */
    public function getRelationsForDiff(): array
    {
        return [
            'receipt' => (
                new MediaCollection($this->getMedia(self::EXPENSE_COLLECTION_NAME))
            )->getAttributesForDiff(),
        ];
    }
}
