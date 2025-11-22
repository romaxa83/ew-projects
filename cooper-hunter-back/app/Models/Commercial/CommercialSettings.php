<?php

namespace App\Models\Commercial;

use App\Contracts\Media\HasMedia;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\Commercial\CommercialSettingsFactory;

/**
 * @property string|null quote_title
 * @property string|null quote_address_line_1
 * @property string|null quote_address_line_2
 * @property string|null quote_phone
 * @property string|null quote_email
 * @property string|null quote_site
 * @property string|null nextcloud_link
 *
 * @method static CommercialSettingsFactory factory(...$parameters)
 */
class CommercialSettings extends BaseModel implements HasMedia
{
    use HasFactory;
    use Filterable;
    use InteractsWithMedia;

    public const TABLE = 'commercial_settings';
    public const MEDIA_RDP = 'media_rdp';
    public const MEDIA_PDF = 'media_pdf';

    public $timestamps = false;

    protected $table = self::TABLE;

    public static function rdpRule(): string
    {
        return 'mimetypes:' . implode(
                ',',
                array_merge(
                    [
                        'rdp',
                        'application/rdp',
                        'application/x-rdp',
                    ],
                    self::mimeArchive()
                )
            );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_RDP)
            ->singleFile();

        $this->addMediaCollection(self::MEDIA_PDF)
            ->acceptsMimeTypes($this->mimePdf())
            ->singleFile();
    }
}
