<?php

namespace App\Models\Support\RequestSubjects;

use App\Models\BaseModel;
use App\Traits\HasFactory;

/**
 * @property int id
 * @property string slug
 * @property string title
 * @property string|null description
 * @property int row_id
 * @property string|null language
 */
class SupportRequestSubjectTranslation extends BaseModel
{
    use HasFactory;

    public const TABLE = 'support_request_subject_translations';

    public $timestamps = false;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'row_id',
        'language'
    ];
}
