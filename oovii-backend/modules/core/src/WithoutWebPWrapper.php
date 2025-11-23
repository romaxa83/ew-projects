<?php

namespace WezomCms\Core;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Traits\Model\ImageAttachable;

/**
 * Class WithoutWebPWrapper
 * @package WezomCms\Core
 * @mixin ImageAttachable
 */
class WithoutWebPWrapper
{
    /**
     * @var Model
     */
    private $model;

    /**
     * WithoutWebPWrapper constructor.
     * @param  Model  $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return ImageService::withoutWebp(function () use ($name, $arguments) {
            return call_user_func([$this->model, $name], ...$arguments);
        });
    }
}
