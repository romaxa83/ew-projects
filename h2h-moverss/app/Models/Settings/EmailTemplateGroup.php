<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Settings\EmailTemplateGroup
 *
 * @property int $id
 * @property int $active
 * @property int|null $division_id
 * @property int|null $sort
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Settings\EmailTemplate[] $groupRecords
 * @property-read int|null $group_records_count
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup records($division_id)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateGroup whereDivisionId($value)
 * @mixin \Eloquent
 */
class EmailTemplateGroup extends Model
{
    protected $table = 'email_templates_groups';

    public function groupRecords()
    {
        return $this->hasMany(EmailTemplate::class, 'group_id', 'id');
    }

    public function scopeRecords($q, $division_id)
    {
        $q
            ->where('division_id', $division_id)
            ->with([
                'groupRecords' => function ($q) use ($division_id) {
                    $q->records($division_id);
                }
            ])
            ->orderBy('sort');
    }
}
