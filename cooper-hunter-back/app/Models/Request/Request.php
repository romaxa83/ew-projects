<?php

namespace App\Models\Request;

use App\Enums\Requests\RequestCommand;
use App\Models\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string driver
 * @property RequestCommand command
 * @property string url
 * @property string status
 * @property array|null send_data
 * @property array|null response_data
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 */
class Request extends BaseModel
{
    public const SEND    = 'send';
    public const SUCCESS = 'success';
    public const ERROR   = 'error';

    public const DRIVER_ONEC = 'onec';

    public const TABLE = 'requests';
    protected $table = self::TABLE;

    protected $casts = [
        'command' => RequestCommand::class,
        'send_data' => 'array',
        'response_data' => 'array',
    ];

    protected $fillable = [
        'response_data',
        'status',
    ];

    public static function create(
        $driver,
        $command,
        $url,
        $sendData,
    ): self
    {
        $model = new self();
        $model->driver = $driver;
        $model->command = $command;
        $model->url = $url;
        $model->status = self::SEND;
        $model->send_data = $sendData;
        $model->save();
        return $model;
    }
}

