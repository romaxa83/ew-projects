<?php

namespace App\Models\History;

use App\Models\BaseModel;
use App\Models\Media\File;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $row_id
 * @property string|null $aa_uuid
 * @property string|null $address
 * @property float|null $amount_including_vat
 * @property float|null $amount_vat
 * @property float|null $amount_without_vat
 * @property string|null $author
 * @property string|null $contact_information
 * @property Carbon|null $date
 * @property float|null $discount
 * @property string|null $etc
 * @property string|null $number
 * @property string|null $organization
 * @property string|null $phone
 * @property string|null $shopper
 * @property string|null $tax_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read InvoicePart[]|Collection $parts
 */
class Invoice extends BaseModel
{
    use HasFactory;

    public const TABLE = 'history_invoices';
    protected $table = self::TABLE;

    protected $dates = [
        'date',
    ];

    public function parts(): HasMany
    {
        return $this->hasMany(InvoicePart::class, 'row_id', 'id');
    }

    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'entity');
    }

    public function fileUploadDir(): string
    {
        return "files/invoice-history/{$this->id}";
    }

    public function fileName(string $type, string $ext = 'pdf'): string
    {
        return "{$type}_{$this->aa_uuid}.{$ext}";
    }

    public function storagePath(string $type, string $ext = 'pdf'): string
    {
        return "{$this->fileUploadDir()}/{$this->fileName($type, $ext)}";
    }
}
