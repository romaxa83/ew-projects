<?php

namespace App\Models\Library;

use App\ModelFilters\Library\LibraryDocumentFilter;
use App\Models\Files\HasMedia;
use App\Models\Users\User;
use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * @property mixed match_policy
 * @property mixed user_id
 * @property mixed owner_id
 * @property string name
 * @property mixed original_name
 * @property mixed user
 * @property mixed owner
 */
class LibraryDocument extends Model implements HasMedia
{
    use Filterable;
    use HasMediaTrait;
    use SetCompanyId;

    public const TABLE_NAME = 'library_documents';

    public const MAX_FILE_SIZE = 10000;
    public const ALLOWED_FILE_TYPES = 'pdf,png,jpeg,doc,docx,txt,xls,xlsx';
    public const MEDIA_COLLECTION_NAME = 'library';

    public $fillable = [
        'name',
        'match_policy',
        'user_id',
        'owner_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    public function user(): BelongsTo
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class);

        return $belongsTo->withTrashed();
    }

    public function owner(): BelongsTo
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class);

        return $belongsTo->withTrashed();
    }

    public function getPolicy(): string
    {
        return $this->match_policy === 0 ? 'public' : 'private';
    }

    public function isDownloadedByTheUser($userId): bool
    {
        return $this->owner_id === $userId;
    }

    public function isSharedToTheUser($user): bool
    {
        if (in_array($this->owner->getRoleName(), User::DRIVER_ROLES) && $user->id === $this->owner->owner->id) {
            return true;
        }
        return $this->user_id === $user->id;
    }

    public function scopePublic($query)
    {
        return $query->where('match_policy', 0);
    }

    public function getWhom()
    {
        if ($this->isPublic()) {
            return __('All drivers');
        }

        if ($this->owner && in_array($this->owner->getRoleName(), User::DRIVER_ROLES) && !empty($this->owner->owner->id)) {
            return $this->owner->owner->getRoleName() . ' - ' . $this->owner->owner->full_name;
        }

        if ($this->user) {
            return $this->user->getRoleName() . ' - ' . $this->user->full_name;
        }

        return '';
    }

    public function isPublic(): bool
    {
        return $this->match_policy === 0;
    }

    public function scopePrivate($query)
    {
        return $query->where('match_policy', 1);
    }

    public function scopeOnlyMyPrivate($query, $userId)
    {
        return $query->where('match_policy', 1)->where('owner_id', $userId);
    }

    public function scopeOrMyDriversPrivate($query, $users)
    {
        return $query->orWhere(
            function ($builder) use ($users) {
                return $builder->where('match_policy', 1)
                    ->whereIn('user_id', $users);
            }
        );
    }

    public function scopeOrMyDriversPrivateSearch($query, $users, $search)
    {
        return $query->orWhere(
            function ($builder) use ($users, $search) {
                return $builder->where('name', 'like', '%' . escapeLike($search) . '%')
                    ->whereIn('user_id', $users);
            }
        );
    }

    public function modelFilter(): string
    {
        return LibraryDocumentFilter::class;
    }

}
