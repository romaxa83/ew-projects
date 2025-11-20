<?php

namespace App\Models\JD;

use App\ModelFilters\JD\ClientFilter;
use App\Models\BaseModel;
use App\Repositories\JD\ModelDescriptionRepository;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yadakhov\InsertOnDuplicateKey;

/**
 * @property int $id
 * @property int $jd_id
 * @property string $customer_id
 * @property string $company_name
 * @property string $customer_first_name
 * @property string $customer_last_name
 * @property string $customer_second_name
 * @property string $phone
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $region_id
 *
 * @property-read  Collection|Region[] $region
 */

class Client extends BaseModel
{
    use InsertOnDuplicateKey;
    use Filterable;

    const TABLE = 'jd_clients';
    protected $table = self::TABLE;

    protected $fillable = [
        'status',
        'company_name'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(ClientFilter::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->customer_first_name} {$this->customer_last_name} {$this->customer_second_name}";
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'jd_id');
    }

    public function modelDescription()
    {
        if (isset($this->pivot->model_description_id)){
            return \App::make(ModelDescriptionRepository::class)->getBy('id', $this->pivot->model_description_id);
        }

        return null;
    }

    public function modelDescriptionName(): null|string
    {
        if($md = $this->modelDescription()){
            return $md->name;
        }

        return null;
    }
}
