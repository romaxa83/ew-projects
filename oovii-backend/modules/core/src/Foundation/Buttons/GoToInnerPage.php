<?php

namespace WezomCms\Core\Foundation\Buttons;

use Illuminate\Database\Eloquent\Model;

class GoToInnerPage extends Button
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * View path.
     *
     * @var string
     */
    protected $view = 'cms-core::admin.directives.gotosite';

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param  Model  $model
     * @return GoToInnerPage
     */
    public function setModel(Model $model): GoToInnerPage
    {
        $this->model = $model;

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
            return view($this->getView(), ['obj' => $this->getModel()]);
        } catch (\Throwable $e) {
            report($e);

            return '';
        }
    }
}
