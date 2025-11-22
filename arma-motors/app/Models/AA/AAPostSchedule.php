<?php

namespace App\Models\AA;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * свободные слоты времени , присланные системой АА
 *
 * @property int $id
 * @property string $post_id    // к какому посту отностится слот
 * @property int $date          // дата
 * @property string $start_work // начало работы поста
 * @property string $end_work   // конец работы поста
 * @property bool $work_day     // рабочий или нет день
 */
class AAPostSchedule extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'aa_post_schedules';
    protected $table = self::TABLE;

    protected $cats = [
        'work_day' => 'boolean',
    ];

    protected $dates = [
        'start_work',
        'end_work',
        'date',
    ];
}


