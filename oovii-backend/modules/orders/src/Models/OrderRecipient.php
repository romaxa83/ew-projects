<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use WezomCms\Orders\Contracts\ClientData;

/**
 * \WezomCms\Orders\Models\OrderRecipient
 *
 * @property int $id
 * @property int $order_id
 * @property bool $recipient_is_me
 * @property string|null $name
 * @property string|null $surname
 * @property string|null $patronymic
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read Order|null $order
 * @method static Builder|OrderRecipient newModelQuery()
 * @method static Builder|OrderRecipient newQuery()
 * @method static Builder|OrderRecipient query()
 * @method static Builder|OrderRecipient whereComment($value)
 * @method static Builder|OrderRecipient whereCreatedAt($value)
 * @method static Builder|OrderRecipient whereId($value)
 * @method static Builder|OrderRecipient whereName($value)
 * @method static Builder|OrderRecipient whereOrderId($value)
 * @method static Builder|OrderRecipient wherePatronymic($value)
 * @method static Builder|OrderRecipient wherePhone($value)
 * @method static Builder|OrderRecipient whereRecipientIsMe($value)
 * @method static Builder|OrderRecipient whereSurname($value)
 * @method static Builder|OrderRecipient whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderRecipient extends Model implements ClientData
{
    use HasFactory;

    public const TABLE = 'order_recipients';

    protected $table = self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['recipient_is_me', 'name', 'surname', 'patronymic', 'phone', 'email', 'comment'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['recipient_is_me' => 'bool'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return string
     */
    public function getFullNameAttribute()
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
}
