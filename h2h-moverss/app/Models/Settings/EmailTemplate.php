<?php

namespace App\Models\Settings;

use App\Models\Emails\MandrillTemplates;
use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * App\Models\Settings\EmailTemplate
 *
 * @property int $id
 * @property int|null $division_id
 * @property int|null $group_id
 * @property int $active
 * @property int|null $sort
 * @property string $title
 * @property int $mailjet_tpl_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate records($division_id)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereMailjetTplId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereDivisionId($value)
 * @property int|null $mandrill_template_id
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate ofDivision($divisionID)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereMandrillTemplateId($value)
 * @property-read MandrillTemplates|null $mandrill
 * @mixin \Eloquent
 */
class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    public function scopeRecords($q, $division_id)
    {
        return $q
            ->whereActive(1)
            ->where('mailjet_tpl_id', '>', 0)
            ->where('division_id', $division_id)
            ->with('mandrill')
            ->select(['id', 'mailjet_tpl_id', 'title', 'group_id'])
            ->orderBy('sort');
    }

    public function mandrill()
    {
        return $this->hasOne(MandrillTemplates::class, 'template_id', 'id');
    }

}
