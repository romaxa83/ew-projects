<?php

namespace App\Models;

use App\Events\ClientUpdated;
use App\Helpers\DbConnections;
use App\Models\Client\ClientToTag;
use App\Services\Communications\RecordCreateService;
use App\Utils\UpdateRelationsTrait;
use Auth;
use Database\Factories\Clients\ClientFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\{Builder, Collection, Factories\HasFactory, Model, Relations\HasMany, SoftDeletes};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Client
 *
 * @property int $id
 * @property string $name
 * @property string|null $lname
 * @property int $ext_id
 * @property \Illuminate\Support\Carbon created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Collection|\App\Models\Client\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read Collection|\App\Models\Client\Email[] $emails
 * @property-read int|null $emails_count
 * @property-read mixed $full_name
 * @property-read Collection|\App\Models\Client\Messenger[] $messengers
 * @property-read int|null $messengers_count
 * @property-read Collection|\App\Models\Client\Notes[] $notes
 * @property-read int|null $notes_count
 * @property-read Collection|\App\Models\Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Collection|\App\Models\Client\Phone[] $phones
 * @property-read int|null $phones_count
 * @property-read Collection|\App\Models\Client\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static Builder|Client active()
 * @method static Builder|Client clientCard()
 * @method static Builder|Client clientEmails()
 * @method static Builder|Client clientPhones()
 * @method static Builder|Client newModelQuery()
 * @method static Builder|Client newQuery()
 * @method static \Illuminate\Database\Query\Builder|Client onlyTrashed()
 * @method static Builder|Client query()
 * @method static Builder|Client whereCreatedAt($value)
 * @method static Builder|Client whereDeletedAt($value)
 * @method static Builder|Client whereExtId($value)
 * @method static Builder|Client whereId($value)
 * @method static Builder|Client whereLname($value)
 * @method static Builder|Client whereName($value)
 * @method static Builder|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Client withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Client withoutTrashed()
 * @method static ClientFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Client extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use UpdateRelationsTrait;
    use HasFactory;

    protected $connection = DbConnections::DEFAULT;

    public const MORPH_NAME = 'client';

    public const TABLE = 'clients';
    protected $table = self::TABLE;

    protected $dates = [
        'deleted_at'
    ];

    protected $fillable = [
        'name',
        'lname'
    ];

    protected $dispatchesEvents = [
        'saving' => ClientUpdated::class,
    ];

    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    /**
     * Активные записи.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function phones()
    {
        return $this->hasMany(Client\Phone::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function messengers()
    {
        return $this->hasMany(Client\Messenger::class);
    }

    public function emails()
    {
        return $this->hasMany(Client\Email::class);
    }

    public function notes()
    {
        return $this->hasMany(Client\Notes::class);
    }

    public function tags()
    {
        return $this->belongsToMany(
            Client\Tag::class,
            'clients_2_tags',
            'client_id',
            'tag_id'
        )
            ->withPivot('employee_id', 'employee_name', 'attached_at')
            ->using(ClientToTag::class)
            ;
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Client\Activity::class);
    }

    public function scopeClientPhones($q): void
    {
        $q->with([
            'phones' => function ($q) {
                $q
                    ->select(['id', 'client_id', 'type_id', 'is_primary', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
            },
        ]);
    }

    public function scopeClientEmails($q): void
    {
        $q->with([
            'emails' => function ($q) {
                $q
                    ->select(['id', 'client_id', 'is_primary', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
            },
        ]);
    }

    public function ClientShortName(): string
    {
        return $this->attributes['name'] . ' ' .
            ($this->attributes['lname'] ? mb_substr($this->attributes['lname'], 0, 1) . '.' : '');
    }

    public function ClientFullName(): string
    {
        return $this->attributes['name'] . ' ' . $this->attributes['lname'];
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->lname;
    }

    public function getClient($id)
    {
        return $this
            ->with([
                'phones' => function ($q) {
                    return $q
                        ->select(['id', 'client_id', 'type_id', 'is_primary', 'value'])
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'emails' => function ($q) {
                    return $q
                        ->select(['id', 'client_id', 'is_primary', 'value'])
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'messengers:id,client_id,type_id,value',
                'notes:id,client_id,user_id,value,created_at',
                'tags'
            ])
            ->withCount(['orders' => function (Builder $q) {
                $q->where('division_id', session('division')['id']);
            }])
            ->find($id);
    }


    public function scopeClientCard(Builder $query)
    {
        $query->with([
            'phones' => function ($q) {
                return $q
                    ->select(['id', 'client_id', 'type_id', 'is_primary', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
            },
            'emails' => function ($q) {
                return $q
                    ->select(['id', 'client_id', 'is_primary', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
            },
            'messengers:id,client_id,type_id,value',
            'notes:id,client_id,user_id,value,created_at',
            'tags'
        ])->withCount(['orders' => function (Builder $q) {
            $q->where('division_id', session('division')['id']);
        }]);
    }

    /**
     * Обновить активити.
     * @param string $type Тип
     * @param array $miscs Данные которые менялись
     */
    public function addActivity($type, $miscs)
    {
        if ($this->id) {
            $act = new Client\Activity();
            $act->type = $type;
            $act->client_id = $this->id;
            $act->user_id = Auth::user()->id ?? 0;
            $act->miscs = $miscs;
            $act->save();

            $additional = [];
            if(isset($miscs['order_id'])){
                $additional['order_id'] = $miscs['order_id'];
            }

            RecordCreateService::handler($act, $additional);
        }
    }

    public function autocompleteCount($term = '')
    {
        if (!$term) {
            return $this->count();
        } else {
            return $this
                ->orWhereRaw("CONCAT(`name`, ' ', `lname`) LIKE ?", ['%' . $term . '%'])
                ->orWhere(function ($query) use ($term) {
                    $query->whereHas('emails', function ($q) use ($term) {
                        $q->where('value', 'like', '%' . $term . '%');
                    })->orWhereHas('phones', function ($q) use ($term) {
                        $q->where('value', 'like', '%' . $term . '%');
                    });
                })
                ->count();
        }
    }


    /**
     * Возвращаем массив. Ключ ID клиента  и маркер того где что нашли
     * @param Collection $Finded
     * @param $term
     * @return Collection
     */
    public static function markFindedClientsEntities(Collection $Finded, $term)
    {
        $Finded = $Finded->map(function (Client $Client) use ($term) {
            $client = ['key' => $Client->id, 'finded' => ''];
            if (Str::contains($Client->id, $term)) {
                $client['finded'] .= 'CustomerID: ' . Str::replaceFirst($term, "<mark>{$term}</mark>", $Client->id) . '. ';
            }
            if (Str::contains(Str::lower($Client->fullName), $term)) {
                $client['finded'] .= 'Name: ' . Str::replaceFirst($term, "<mark>{$term}</mark>", Str::lower($Client->fullName)) . '. ';
            }
            if ($Client->phones)
                foreach ($Client->phones as $phone) {
                    if (Str::contains($phone->value, $term)) {
                        $client['finded'] .= 'Phone: +1' . Str::replaceFirst($term, "<mark>{$term}</mark>", $phone->value) . '. ';
                    }
                }
            if ($Client->emails)
                foreach ($Client->emails as $email) {
                    if (Str::contains(Str::lower($email->value), $term)) {
                        $client['finded'] .= 'Email: ' . Str::replaceFirst($term, "<mark>{$term}</mark>", Str::lower($email->value)) . '. ';
                    }
                }
            return $client;
        });
        return $Finded;
    }

    public function searchCustomerWithRequest(Request $request)
    {
        return $this
            ->with(['emails:id,client_id,value', 'phones:id,client_id,value'])
            ->when($request->get('interface'), function (Builder $q, $interface) {
                if ($interface == 'clients') {
                    $q->where(function ($q) {
                        $q->whereHas('orders', function ($q) {
                            $q->whereIn('division_id', session()->get('division.allowed'));
                        })->orDoesntHave('orders');
                    });
                } elseif ($interface == 'orders') {
                    $q->whereHas('orders', function ($q) {
                        $q->where('division_id', session()->get('division.id'));
                    });
                }
            })
            ->when($request->q, function ($q, $term) {
                $q->where(function ($query) use ($term) {
                    $term = strip_tags($term);
                    preg_match('#([a-z]+)#i', $term, $ma);
                    $num = !$ma ? preg_replace('/[^0-9]/', '', $term) : '';
                    $query
                        ->whereRaw("CONCAT(`name`, ' ', `lname`) LIKE ?", ['%' . $term . '%'])
                        ->orWhereHas('emails', function ($q) use ($term) {
                            $q->where('value', 'like', '%' . $term . '%');
                        });
                    if ($num) {
                        $query->orWhere('id', $num);
                        $query->orWhereHas('phones', function ($q) use ($num) {
                            $q->where('value', 'like', '%' . $num . '%');
                        });
                    }
                });
            });
    }


    /**
     * Find client by: ID, Name, Lname, Email, Phone.
     * @param Request $request
     * @return array
     */
    public function autocomplete(Request $request): array
    {
        $paginator = $this->searchCustomerWithRequest($request)
            ->orderBy('id', 'DESC')
            ->paginate(15, ['id', 'name', 'lname']);

        return [
            'results' => $paginator->items(),
            'pagination' => [
                'more' => $paginator->hasMorePages(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * Найти Клиента по данным или создать нового.
     * @param array $clientRows
     * @param array|string|null $emails
     * @param array|string|null $phones
     * @return int Client ID
     */
    public function searchOrCreate(
        array             $clientRows,
        array|string|null $emails = [],
        array|string|null $phones = []
    ): int
    {
        if ($emails && is_string($emails)) {
            $emails = [$emails];
        }
        if ($phones && is_string($phones)) {
            $phones = [$phones];
        }

        $find = self::query()
            ->when($emails, function ($q, $emails) {
                $q->whereHas('emails', function ($query) use ($emails) {
                    $query->whereIn('value', $emails);
                });
            })
            ->when($phones, function ($q, $phones) {
                $q->whereHas('phones', function ($query) use ($phones) {
                    $query->whereIn('value', $phones);
                });
            })
            ->first('id');

        $client_id = ($emails || $phones) && $find ? $find->id : 0;

        if (!$client_id) {
            // Create new
            $client = new self($clientRows);
            $client->save();

            $client_id = $client->id;

            // Пробуем дообновить данные номеров тел. клиента и Email
            if ($emails) {
                foreach ($emails as $k => $v) {
                    $client->emails()
                        ->firstOrCreate([
                            'value' => $v,
                            'is_primary' => !$k ? 1 : 0
                        ]);
                }
            }

            if ($phones) {
                foreach ($phones as $k => $v) {
                    $client->phones()
                        ->firstOrCreate([
                            'value' => $v,
                            'is_primary' => !$k ? 1 : 0,
                            'type_id' => 1
                        ]);
                }
            }
        }

        return $client_id;
    }

    public function getClientsDT(Request $request): LengthAwarePaginator
    {
        return $this->with([
            'phones' => function ($q) {
                return $q
                    ->select(['client_id', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
            },
            'emails' => function ($q) {
                return $q
                    ->select(['client_id', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
            },
            'tags',
        ])
            ->withCount(['phones', 'emails', 'orders'])
            ->when($request->filled('filters.ids'), function ($q) use ($request) {
                $q->whereIn('id', $request->filters['ids']);
            })
            ->when($request->filled('filters.filter.clientTags'), function ($q) use ($request) {
                $q->whereHas('tags', fn($q) => $q->whereIn('tag_id', $request->filters['filter']['clientTags']));
            })
            ->orderBy('id', $request->order[0]['dir'])
            ->paginate($request->get('length'), ['*'], 'page', $request->start / $request->length + 1);
    }

}
