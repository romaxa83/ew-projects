<?php

namespace WezomCms\Core\Foundation\Buttons;

use Illuminate\Database\Eloquent\Model;

class RestoreTrashed extends Button
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string|null
     */
    protected $routeName;

    /**
     * View path.
     *
     * @var string
     */
    protected $view = 'cms-core::admin.partials.buttons.restore-trashed';

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param  Model  $model
     * @return RestoreTrashed
     */
    public function setModel(Model $model): RestoreTrashed
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    /**
     * @param  string  $routeName
     *
     * @return RestoreTrashed
     */
    public function setRouteName(string $routeName): RestoreTrashed
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * Generate button or link.
     *
     * @return string|mixed
     */
    public function render()
    {
        try {
            return view($this->getView(), ['button' => $this]);
        } catch (\Throwable $e) {
            report($e);

            return '';
        }
    }
}
