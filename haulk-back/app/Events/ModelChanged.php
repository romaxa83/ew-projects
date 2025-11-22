<?php

namespace App\Events;

use App\Services\Histories\HistoryHandlerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class ModelChanged
{
    use SerializesModels;

    public $model;
    public $message;
    public $meta;
    public $performed_at;
    public $performed_timezone;

    public $handler;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param string $message
     * @param array $meta
     *
     * @return void
     */
    public function __construct($model, $message, $meta = null, ?int $performed_at = null, ?string $performed_timezone = null, HistoryHandlerInterface $handler = null)
    {
        $this->model = $model;
        $this->message = $message;
        $this->meta = $meta;
        $this->performed_at = $performed_at;
        $this->performed_timezone = $performed_timezone;
        $this->handler = $handler;
    }
}
