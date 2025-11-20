<?php

namespace WezomCms\Articles\Http\Controllers\Admin;

use Illuminate\Http\Request;
use WezomCms\Articles\Http\Requests\Admin\ArticleGroupRequest;
use WezomCms\Articles\Models\ArticleGroup;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\MetaFields\SeoFields;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\SiteLimit;
use WezomCms\Core\Traits\SettingControllerTrait;

class ArticleGroupsController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = ArticleGroup::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-articles::admin.groups';

    /**
     * Resource route name.
     *
     * @var string
     */

    protected $routeName = 'admin.article-groups';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = ArticleGroupRequest::class;

    /**
     * @param $result
     * @param  Request  $request
     */
    protected function selectionIndexResult($result, Request $request)
    {
        $result->orderBy('sort')->latest('id');
    }

    /**
     * Controller name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-articles::admin.Article groups');
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings()
    {
        return [
            SiteLimit::make()->setName(__('cms-articles::admin.Site article groups limit at page')),
            SeoFields::make('Articles'),
            AdminLimit::make(),
        ];
    }
}
