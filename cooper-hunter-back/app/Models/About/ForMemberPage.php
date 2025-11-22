<?php

namespace App\Models\About;

use App\Contracts\Media\HasMedia;
use App\Enums\About\ForMemberPageEnum;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use BenSampo\Enum\Traits\CastsEnums;
use Database\Factories\About\ForMemberPageFactory;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int id
 * @property string for_member_type
 *
 * @method static ForMemberPageFactory factory(...$parameters)
 */
class ForMemberPage extends BaseModel implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;
    use CastsEnums;

    public const TABLE = 'for_member_pages';
    public const MORPH_NAME = 'for_member_page';
    public const MEDIA_COLLECTION_NAME = 'for_member_page';

    public $timestamps = false;

    protected $casts = [
        'for_member_type' => ForMemberPageEnum::class,
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes($this->mimeImage())
            ->singleFile();
    }

    public function scopeForMemberType(Builder|self $q, ForMemberPageEnum $type): void
    {
        $q->where('for_member_type', $type->value);
    }
}
