<?php

namespace App\Models\AA;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $entity_type
 * @property int $entity_id
 * @property string $model
 * @property string $type
 *
 * @property string $url
 * @property string $message
 * @property boolean $status
 * @property string $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 */
class AAResponse extends BaseModel
{
    public const TABLE_NAME = 'aa_responses';
    protected $table = self::TABLE_NAME;

    public const TYPE_SIGNUP           = 'signup';
    public const TYPE_CREATE_USER      = 'create user';
    public const TYPE_UPDATE_USER      = 'update user';
    public const TYPE_GET_CAR          = 'get car';
    public const TYPE_CREATE_CAR       = 'create car';
    public const TYPE_CREATE_ORDER     = 'create order';
    public const TYPE_ACCEPT_AGREEMENT = 'accept agreement';
    public const TYPE_GET_INVOICE      = 'get invoice'; // счет
    public const TYPE_GET_ACT          = 'get act';     // act

    public const STATUS_SUCCESS       = 'success';          // все четко
    public const STATUS_ERROR         = 'error';            // произошла ошибка от AA
    public const STATUS_ERROR_IN_SAVE = 'error_in_save';    // произошла ошибка в процессе сохранения данных

    protected $casts = [
        'data' => 'array',
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}

