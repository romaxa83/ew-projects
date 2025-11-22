<?php

namespace App\Models\Clients;

use App\Contracts\Models\HasModeration;
use App\Enums\Clients\BanReasonsEnum;
use App\Filters\Clients\ClientFilter;
use App\Models\BaseModel;
use App\Models\Managers\Manager;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasPhones;
use App\Traits\Model\ModeratedScopeTrait;
use Database\Factories\Clients\ClientFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static ClientFactory factory()
 */
class Client extends BaseModel implements HasModeration
{
    use HasFactory;
    use HasPhones;
    use Filterable;
    use ModeratedScopeTrait;

    public const ALLOWED_SORTING_FIELDS = [
        'name',
        'contact_person'
    ];

    protected $fillable = [
        'name',
        'contact_person',
        'manager_id',
        'edrpou',
        'inn',
        'ban_reason',
        'show_ban_in_inspection'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'ban_reason' => BanReasonsEnum::class,
        'show_ban_in_inspection' => 'bool'
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'manager_id', 'id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(ClientFilter::class);
    }

    public function shouldModerated(): bool
    {
        return !$this->isModerated();
    }
}
