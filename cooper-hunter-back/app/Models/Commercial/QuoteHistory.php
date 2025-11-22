<?php

namespace App\Models\Commercial;

use App\Contracts\Media\HasMedia;
use App\Filters\Commercial\CommercialQuoteHistoryFilter;
use App\Models\Admins\Admin;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property integer admin_id
 * @property integer quote_id
 * @property integer position
 * @property string estimate
 * @property string data
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property Admin admin
 */
class QuoteHistory extends BaseModel implements HasMedia
{
    use HasFactory;
    use Filterable;
    use InteractsWithMedia;

    public const TABLE = 'commercial_quote_histories';
    protected $table = self::TABLE;

    public const MEDIA_COLLECTION_NAME = 'pdfs';
    public const MORPH_NAME = 'pdf';

    protected $casts = [
        'data' => 'array',
    ];

    public function modelFilter(): string
    {
        return CommercialQuoteHistoryFilter::class;
    }

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(array_merge(
                $this->mimePdf(),
            ))
            ->singleFile();
    }

    public function admin(): BelongsTo|Admin
    {
        return $this->belongsTo(Admin::class);
    }

    public function getPdfStoragePath(): string
    {
        return storage_path("Estimate{$this->estimate}.pdf");
    }

    public function getPdfPath(): ?string
    {
        if($media = $this->media->first()){
            $path = storage_path("app/public/{$media->id}/{$media->file_name}");
            if(file_exists($path)){
                return $path;
            }
        }
        return null;
    }
}
