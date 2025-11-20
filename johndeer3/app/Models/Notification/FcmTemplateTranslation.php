<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Comment
 *
 * @property int $id
 * @property int $model_id
 * @property string $lang
 * @property string $title
 * @property string $text
 */

class FcmTemplateTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'notification_template_translations';
}
