<?php

namespace WezomCms\Users\Http\Controllers\Admin;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Input;
use WezomCms\Core\Settings\Fields\Number;
use WezomCms\Core\Settings\MetaFields\Heading;
use WezomCms\Core\Settings\MetaFields\Title;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\PageName;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Users\Http\Requests\Admin\CreateUserRequest;
use WezomCms\Users\Http\Requests\Admin\UpdateUserRequest;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Models\User;

class CarController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Car::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-users::admin.cars';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.user-cars';

    protected $hideCreateBnt = true;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-users::admin.Users cars');
    }

    protected function selectionIndexResult($query, Request $request)
    {
        return $query->active();
    }


    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        $result = [];

        $tabs = new RenderSettings(
            new Tab('page-settings', __('cms-users::admin.Car tab settings'), 1, 'fa-folder-o')
        );

        $result[] = Number::make($tabs)
            ->setName(__('cms-users::admin.Count cars for user'))
            ->setSort(1)
            ->setKey('count-cars-for-user')
            ->setRules('nullable|integer');

        return $result;

    }
}
