<?php

namespace WezomCms\Pages\Http\Controllers\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Pages\Http\Requests\Admin\PageRequest;
use WezomCms\Pages\Models\Page;

class PagesController extends AbstractCRUDController
{
    use SettingControllerTrait;

    protected $model = Page::class;

    protected $view = 'cms-pages::admin';

    protected $routeName = 'admin.pages';

    protected $request = PageRequest::class;

    protected function title(): string
    {
        return __('cms-pages::admin.Pages');
    }

    protected function settings(): array
    {
        return [AdminLimit::make()];
    }

    protected function editViewData($model, array $viewData): array
    {
        return [
            'types' => Page::list(),
            'selectedType' => [$model->type],
        ];
    }

    protected function createViewData($model, array $viewData): array
    {
        return [
            'types' => Page::list(),
            'selectedType' => [],
        ];
    }
}
