<?php

namespace App\Models\Projects;

use App\Contracts\Members\Member;
use App\Filters\Projects\ProjectFilter;
use App\Models\BaseModel;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\NormalizeId;
use Database\Factories\Projects\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string member_type
 * @property int member_id
 * @property string name
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Project::member()
 * @property-read Member member
 *
 * @see Project::technicians()
 * @property-read Collection|Technician[] technicians
 *
 * @see Project::systems()
 * @property-read Collection|System[] systems
 *
 * @see Project::scopeWhereMember()
 * @method Builder|static whereMember(Member $member)
 *
 * @see Project::scopeBelongsToUser()
 * @method Builder|static belongsToUser(User|int $member)
 *
 * @see Project::scopeBelongsToTechnician()
 * @method Builder|static belongsToTechnician(Technician|int $member)
 *
 * @method static ProjectFactory factory(...$parameters)
 */
class Project extends BaseModel
{
    use HasFactory;
    use Filterable;
    use NormalizeId;

    public const TABLE = 'projects';
    public const MORPH_NAME = 'project';

    protected $table = self::TABLE;

    public function modelFilter(): string
    {
        return ProjectFilter::class;
    }

    public function member(): MorphTo
    {
        return $this->morphTo();
    }

    public function technicians(): BelongsToMany|Technician
    {
        return $this->belongsToMany(Technician::class);
    }

    public function systems(): HasMany|System
    {
        return $this->hasMany(System::class);
    }

    public function scopeWhereMember(Builder|self $q, Member $member): void
    {
        $q->where(
            [
                'member_type' => $member->getMorphType(),
                'member_id' => $member->getId(),
            ]
        );
    }

    public function scopeBelongsToUser(Builder|self $q, User|int $member): void
    {
        $q->where(
            [
                'member_type' => User::MORPH_NAME,
                'member_id' => $this->normalizeId($member)
            ]
        );
    }

    public function scopeBelongsToTechnician(Builder|self $q, Technician|int $member): void
    {
        $q->where(
            [
                'member_type' => Technician::MORPH_NAME,
                'member_id' => $this->normalizeId($member)
            ]
        );
    }
}
