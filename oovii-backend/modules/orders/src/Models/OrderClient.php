<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use WezomCms\Orders\Contracts\ClientData;
use WezomCms\Users\Models\User;

/**
 * \WezomCms\Orders\Models\OrderClient
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $name
 * @property string|null $surname
 * @property string|null $patronymic
 * @property string|null $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \WezomCms\Orders\Models\Order|null $order
 * @method static Builder|OrderClient newModelQuery()
 * @method static Builder|OrderClient newQuery()
 * @method static Builder|OrderClient query()
 * @method static Builder|OrderClient whereCreatedAt($value)
 * @method static Builder|OrderClient whereEmail($value)
 * @method static Builder|OrderClient whereId($value)
 * @method static Builder|OrderClient whereName($value)
 * @method static Builder|OrderClient whereOrderId($value)
 * @method static Builder|OrderClient wherePatronymic($value)
 * @method static Builder|OrderClient wherePhone($value)
 * @method static Builder|OrderClient whereSurname($value)
 * @method static Builder|OrderClient whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderClient extends Model implements ClientData
{
    use Notifiable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'surname', 'patronymic', 'email', 'phone'];

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', [$this->surname, $this->name, $this->patronymic]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getFullName(): string
    {
        return $this->full_name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function fillFromUser(?User $user): self
    {
        if ($user) {
            $this->name = $user->name;
            $this->surname = $user->surname;
            $this->patronymic = $user->patronymic;
            $this->email = $user->email;
            $this->phone = $user->phone;
        }

        return $this;
    }
}
