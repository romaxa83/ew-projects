<?php

namespace App\Models\Report;

use App\ModelFilters\Report\ReportFilter;
use App\Models\BaseModel;
use App\Models\Comment;
use App\Models\Image;
use App\Models\JD\Client;
use App\Models\JD\EquipmentGroup;
use App\Models\Report\Feature\ReportFeatureValue;
use App\Models\User\User;
use App\Type\ReportStatus;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $title
 * @property string|null $salesman_name
 * @property string|null $assignment
 * @property string|null $result
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $planned_at
 * @property boolean $verify
 * @property boolean $send_push
 * @property string $client_comment
 * @property string $client_email
 * @property string|null $machine_for_compare
 * @property Carbon|null fill_table_date
 * @property-read Location $location
 * @property-read User $user
 * @property-read ReportPushData|null $pushData
 * @property-read Video|null $video
 * @property-read Comment|null comment
 * @property-read Collection|ReportMachine[] $reportMachines
 * @property-read Collection|ReportFeatureValue[] $features
 * @property-read Collection|Client[] $clients
 * @property-read Collection|ReportClient[] $reportClients
 * @property-read Image[]|Collection $images
 *
 * @method static Report|Builder onlyCombine()
 * @method static Report|Builder listFilter(?User $user)
 */

class Report extends BaseModel
{
    use Filterable;
    use HasFactory;

    const DEFAULT_DAY_FOR_PUSH = 7;

    protected $table = 'reports';

    protected $fillable = [
        'status',
        'fill_table_date'
    ];

    protected $casts = [
        'verify' => 'boolean',
        'send_push' => 'boolean',
    ];

    protected $dates = [
        'fill_table_date',
        'planned_at'
    ];

    public function modelFilter()
    {
        return $this->provideFilter(ReportFilter::class);
    }

    public function isOwner(User $user)
    {
        return $this->user_id == $user->id;
    }

    public function isOwnerDealer(User $user)
    {
        return $this->user->dealer->id == $user->dealer->id;
    }

    public function isOpenEdit()
    {
        return $this->status == ReportStatus::OPEN_EDIT;
    }

    public function isProcessCreated()
    {
        return $this->status == ReportStatus::IN_PROCESS;
    }

    public function isEdited()
    {
        return $this->status == ReportStatus::EDITED;
    }

    public function isVerify()
    {
        return $this->status == ReportStatus::VERIFY;
    }

    public function isCreated()
    {
        return $this->status == ReportStatus::CREATED;
    }

    public function hasSignature(): bool
    {
        return $this->images()->where('model', Image::SIGNATURE)->exists();
    }

    // relation
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(Location::class);
    }

    public function pushData(): HasOne
    {
        return $this->hasOne(ReportPushData::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class)
            ->withPivot('type', 'model_description_id', 'quantity_machine');
    }

    public function reportClients(): BelongsToMany
    {
        return $this->belongsToMany(ReportClient::class)
            ->withPivot('type', 'model_description_id', 'quantity_machine');
    }

    public function reportMachines(): BelongsToMany
    {
        return $this->belongsToMany(ReportMachine::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'entity');
    }

    public function comment(): MorphOne
    {
        return $this->morphOne(Comment::class, 'entity');
    }

    public function features(): HasMany
    {
        return $this->hasMany(ReportFeatureValue::class);
    }

    public function scopeOnlyCombine(Builder $query)
    {
        if(null !== request('onlyCombine') && filter_var(request('onlyCombine'), FILTER_VALIDATE_BOOLEAN)){
            return $query->whereHas('reportMachines.equipmentGroup', function($q){
                    $q->whereIn('name', EquipmentGroup::forCombinesStatistic());
            });
        }

        return $query;
    }

    public function scopeListFilter(Builder $query, ?User $user)
    {
        if($user == null || $user->isAdmin()){
            return $query;
        }

        if($user->isPS()){
            return $query->where(function(Builder $query) use ($user){
                $query->where('user_id', $user->id)
                    ->orWhereHas('user', function(Builder $query) use ($user){
                        $query->where('dealer_id', $user->dealer->id);
                    });
                });
        }

        if($user->isSM() && isset($user->dealer->sm->id)){
            $value = $user->dealer->sm->id;
            return $query->whereHas('user.dealer.sm', function(Builder $query) use($value) {
                $query->where('id', $value);
            });
        }

        if($user->isTM() || $user->isTMD()){
            $value = $user->id;
            return $query->whereHas('user.dealer.tm', function(Builder $query) use($value) {
                $query->where('id', $value);
            });
        }
    }

    public function scopeSearch(Builder $query, string $search)
    {
        return $query->whereHas('user.dealer', function(Builder $query) use($search) {
                    $query->where('name', 'like', $search.'%');
                })
                // поиск по Eq model
                ->orWhereHas('reportMachines.equipmentGroup', function(Builder $query) use ($search){
                    $query->where('name', 'like', $search.'%');
                })
                // поиск по SerialNumber
                ->orWhereHas('reportMachines', function(Builder $query) use ($search){
                    $query->where('machine_serial_number', 'like', $search.'%');
                })
                // поиск по Client
                ->orWhereHas('clients', function(Builder $query) use ($search){
                    $query->where('company_name', 'like', $search.'%');
                })
                // поиск по DemoDriverSurname
                ->orWhereHas('user.profile', function (Builder $query) use ($search){
                    $query->where('last_name', 'like', $search.'%');
                })
            ;
    }
}
