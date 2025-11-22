<?php

namespace App\Models\Alerts;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alert extends Model
{
    use HasFactory;

    public const DEVICE_TOGGLE_ACTIVITY = 'device_toggle_activity';
    public const DEVICE_SUBSCRIPTION_CHANGE_RATE = 'device_subscription_change_rate';

    /**
     * @var array
     */
    protected $fillable = [
        'message',
        'type',
        'meta',
        'placeholders',
    ];

    protected $casts = [
        'meta' => 'array',
        'placeholders' => 'array',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        static::addGlobalScope(
            'onlyMine',
            function (Builder $builder) {
                if (auth()->check() && !is_null($user = authUser())) {
                    $builder->where(
                        'recipient_id',
                        $user->id
                    )->orWhere(
                        function (Builder $q) use ($user) {
                            $q->whereNull(
                                'recipient_id'
                            )->whereDoesntHave(
                                'deletedAlerts',
                                function (Builder $q1) use ($user) {
                                    $q1->where('user_id', $user->id);
                                }
                            );
                        }
                    );
                }
            }
        );
    }

    public function deletedAlerts(): HasMany
    {
        return $this->hasMany(DeletedAlert::class);
    }

    public function isPersonal(): bool
    {
        return (bool)$this->recipient_id;
    }
}
