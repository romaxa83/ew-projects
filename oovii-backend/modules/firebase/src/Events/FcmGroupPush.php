<?php

namespace WezomCms\Firebase\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class FcmGroupPush implements PushEvent
{
    use SerializesModels;

    private null|Model $model;
    private string $type;

    public function __construct($type, Model $relatedModel = null)
    {
        $this->type = $type;
        $this->model = $relatedModel;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getModel(): null|Model
    {
        return $this->model;
    }
}
