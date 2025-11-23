<?php

namespace App\Models\Client;

use App\Models\Audit;
use App\Models\Client;
use Database\Factories\Clients\TagFactory;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Exceptions\AuditingException;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Relations\BelongsToMany, SoftDeletes, Model};
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Client\Tag
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Query\Builder|Tag onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Tag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Tag withoutTrashed()
 * @property string|null $color
 * @property string|null $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|Client[] $clients
 * @property-read int|null $clients_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereIcon($value)
 * @property int|null $sort
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereSort($value)
 * @mixin \Eloquent
 * @method static TagFactory factory(...$parameters)
 */
class Tag extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'clients_tags';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'title',
        'icon',
        'color',
        'sort'
    ];

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'clients_2_tags', 'tag_id', 'client_id');
    }

    /**
     * Сохранить теги для профайла.
     * @param  object  $client
     * @param  array  $tags
     * @throws AuditingException
     */
    public function tagsSaver(Client $client, $tags)
    {

        $oldTags = $client->tags->pluck('title')->toArray();

        $existingTags = $client->tags()->get()->keyBy('id');


        $ids = [];
        $finalIds = [];

        $changed = 0;
        foreach ($tags as $v) {
            $upd = $this->updateOrCreate(
                [
                    'id' => $v['key'] ?? null,
                ],
                [
                    'title' => $v['value']
                ]);

            if ($existingTags->has($upd->id)) {
                $finalIds[$upd->id] = [
                    'employee_id' => $existingTags[$upd->id]->pivot->employee_id, // Сохраняем старое значение
                    'employee_name' => $existingTags[$upd->id]->pivot->employee_name, // Сохраняем старое значение
                    'attached_at' => $existingTags[$upd->id]->pivot->attached_at, // Сохраняем старое значение
                ];
            } else {
                // Если тег новый, добавляем его с новыми данными
                $finalIds[$upd->id] = [
                    'employee_id' => auth_user()?->employee?->id ?? null,
                    'employee_name' => auth_user()?->employee?->full_name ?? null,
                    'attached_at' => Carbon::now(), // Устанавливаем текущее время
                ];
                $changed = 1; // Помечаем, что есть изменения
            }


            if ($upd->wasChanged() || $upd->wasRecentlyCreated) {
                $changed = 1;
            }
        }


        $r = $client->auditSync('tags', $finalIds);

        $modelClone = clone $client;
        $modelClone->refresh();
        $newTags = $modelClone->tags->pluck('title')->toArray();

        $audit = Audit::query()
            ->where('event', Audit::EVENT_SYNC)
            ->where('auditable_type', Client::MORPH_NAME)
            ->where('auditable_id', $client->id)
            ->latest()
            ->first();

        if($audit){
            $audit->new_values = array_merge($audit->new_values, ['custom_tags' => $newTags]);
            $audit->old_values = array_merge($audit->old_values, ['custom_tags' => $oldTags]);
            $audit->save();
        }

        if ($r['attached'] || $r['detached'] || $r['updated']) {
            $changed = 1;
        }

        return $changed;
    }

//    /**
//     * Сохранить теги для профайла.
//     * @param  object  $client
//     * @param  array  $tags
//     * @throws AuditingException
//     */
//    public function tagsSaver(Client $client, $tags)
//    {
//
//        $oldTags = $client->tags->pluck('title')->toArray();
//
//        $existingTagIds = $client->tags->pluck('id')->toArray();
//
//        $ids = [];
//        $finalIds = [];
//
//        $changed = 0;
//        foreach ($tags as $v) {
//            $upd = $this->updateOrCreate(
//                [
//                    'id' => $v['key'] ?? null,
//                ],
//                [
//                    'title' => $v['value']
//                ]);
//
//            if(!in_array($upd->id, $existingTagIds)){
//                $ids[$upd->id] = [
//                    'employee_id' => auth_user()?->employee?->id,
//                    'attached_at' => Carbon::now()
//                ];
//            }
//
////            $ids[] = $upd->id;
//
//            if ($upd->wasChanged() || $upd->wasRecentlyCreated) {
//                $changed = 1;
//            }
//        }
//
//        $r = $client->auditSync('tags', $ids, false);
//
//        $modelClone = clone $client;
//        $modelClone->refresh();
//        $newTags = $modelClone->tags->pluck('title')->toArray();
//
//        $audit = Audit::query()
//            ->where('event', Audit::EVENT_SYNC)
//            ->where('auditable_type', Client::MORPH_NAME)
//            ->where('auditable_id', $client->id)
//            ->latest()
//            ->first();
//
//        if($audit){
//            $audit->new_values = array_merge($audit->new_values, ['custom_tags' => $newTags]);
//            $audit->old_values = array_merge($audit->old_values, ['custom_tags' => $oldTags]);
//            $audit->save();
//        }
//
//        if ($r['attached'] || $r['detached'] || $r['updated']) {
//            $changed = 1;
//        }
//
//        return $changed;
//    }

}
