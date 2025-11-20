<?php

namespace App\Models\Notification;

use App\Helpers\ConvertLangToLocale;
use App\ModelFilters\Notification\FcmTemplateFilter;
use App\ModelFilters\Page\PageFilter;
use App\Traits\ActiveTrait;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**t
 *
 * @property int $id
 * @property array $vars
 * @property string $type
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 */

class FcmTemplate extends Model
{
    use Filterable;
    use ActiveTrait;

    const PLANNED = 'planned';
    const POSTPONED = 'postponed';

    protected $table = 'notification_templates';

    protected $casts = [
        'vars' => 'array',
        'active' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(FcmTemplateFilter::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FcmTemplateTranslation::class, 'model_id', 'id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(FcmTemplateTranslation::class,'model_id', 'id')
            ->where('lang', ConvertLangToLocale::convert(\App::getLocale()));
    }

}
