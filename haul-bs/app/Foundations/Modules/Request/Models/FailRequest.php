<?php

namespace App\Foundations\Modules\Request\Models;

use App\Foundations\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int id
 * @property string model_type
 * @property int model_id
 * @property string type
 * @property string|null reason
 * @property array data
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class FailRequest extends BaseModel
{
    public const TABLE = 'failed_requests';
    protected $table = self::TABLE;

    public const ECOM_TYPE = 'e-commerce';

    /** @var array<string, string> */
    protected $casts = [
        'data' => 'array',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public static function create(
        string $type,
        array $data,
        $modelId = null,
        $modelType = null,
        $reason = null,
    ): void
    {
        $model = new self();
        $model->type = $type;
        $model->model_type = $modelType;
        $model->model_id = $modelId;
        $model->reason = $reason;
        $model->data = $data;

        $model->save();
    }
}
