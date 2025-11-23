<?php

namespace App\Models;

use App\Models\Twilio\TwilioSms;
use Database\Factories\Attachment\AttachmentFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, SoftDeletes, Model};

/**
 * App\Models\Attachment
 *
 * @property int $id
 * @property int|null $entity_id
 * @property int|null $entity_type
 * @property int $user_id
 * @property string $hash
 * @property mixed|null $miscs
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment newQuery()
 * @method static \Illuminate\Database\Query\Builder|Attachment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Attachment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Attachment withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static AttachmentFactory factory(...$parameters)
 */
class Attachment extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const MORPH_NAME = 'attachment';

    public const TABLE = 'attachments';
    protected $table = self::TABLE;

    protected $fillable = [
        'user_id',
        'hash',
        'description',
        'miscs',
        'entity_id',
        'entity_type',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'miscs' => 'json',
    ];

    protected static function newFactory(): AttachmentFactory
    {
        return AttachmentFactory::new();
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function getUrl(): ?string
    {
        $path = 'storage/' . $this->miscs['file']['patch'] . $this->hash .'.'. $this->miscs['file']['ext'];
        return asset($path);
    }
}
