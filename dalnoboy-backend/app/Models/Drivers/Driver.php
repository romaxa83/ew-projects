<?php

namespace App\Models\Drivers;

use App\Contracts\Models\HasModeration;
use App\Filters\Drivers\DriverFilter;
use App\Models\BaseModel;
use App\Models\Clients\Client;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasPhones;
use App\Traits\Model\ModeratedScopeTrait;
use App\Traits\Model\RuleInTrait;
use Database\Factories\Drivers\DriverFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static DriverFactory factory()
 */
class Driver extends BaseModel implements HasModeration
{
    use HasFactory;
    use HasPhones;
    use Filterable;
    use RuleInTrait;
    use ModeratedScopeTrait;

    public const ALLOWED_SORTING_FIELDS = [
        'full_name'
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'second_name',
        'email',
        'comment',
        'client_id',
        'active',
        'is_moderated'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'active' => 'bool',
        'is_moderated' => 'bool'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(DriverFilter::class);
    }

    public function shouldModerated(): bool
    {
        if (!$this->isModerated()) {
            return true;
        }

        if($this->client){
            return $this->client->shouldModerated();
        }

        return false;
    }
}
