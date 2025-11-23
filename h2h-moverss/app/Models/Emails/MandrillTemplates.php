<?php

namespace App\Models\Emails;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Emails\MandrillTemplates
 *
 * @property int $id
 * @property int|null $template_id
 * @property string|null $template_slug
 * @property string|null $template_vars
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates query()
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates whereTemplateSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates whereTemplateVars($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MandrillTemplates whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MandrillTemplates extends Model
{
    use HasFactory;
    protected $table = 'email_templates_mandrill';
}
