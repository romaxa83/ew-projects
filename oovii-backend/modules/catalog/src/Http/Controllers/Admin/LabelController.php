<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use WezomCms\Catalog\Http\Requests\Admin\LabelRequest;
use WezomCms\Catalog\Models\Labels\Label;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Traits\SettingControllerTrait;

class LabelController extends AbstractCRUDController
{
    use SettingControllerTrait;

    protected $model = Label::class;

    protected $view = 'cms-catalog::admin.labels';

    protected $routeName = 'admin.labels';

    protected $request = LabelRequest::class;

    public function __construct()
    {
        parent::__construct();
    }

    protected function title(): string
    {
        return __('cms-catalog::admin.labels.names');
    }

    protected function settings(): array
    {
        return [AdminLimit::make()];
    }
}


