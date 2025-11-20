<?php

namespace WezomCms\Articles\Http\Controllers\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use WezomCms\Articles\Http\Requests\Admin\ArticleRequest;
use WezomCms\Articles\Models\Article;
use WezomCms\Articles\Models\ArticleGroup;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\MetaFields\SeoFields;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\SiteLimit;
use WezomCms\Core\Traits\SettingControllerTrait;

class ArticlesController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-articles::admin';

    /**
     * Resource route name.
     *
     * @var string
     */

    protected $routeName = 'admin.articles';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = ArticleRequest::class;

    /**
     * @var bool
     */
    private $useGroups;

    /**
     * ArticlesController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->useGroups = (bool) config('cms.articles.articles.use_groups');
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-articles::admin.Articles');
    }

    /**
     * @return string|null
     */
    protected function frontUrl(): ?string
    {
        return $this->useGroups ? null : route('articles');
    }

    /**
     * @param $result
     * @param  Request  $request
     */
    protected function selectionIndexResult($result, Request $request)
    {
        $result->latest('id');
    }

    /**
     * @param  Article  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function formData($obj, array $viewData)
    {
        $groups = [];

        if ($this->useGroups) {
            $groups = ArticleGroup::getForSelect();
        }

        return compact('groups');
    }

    /**
     * @param  Article  $obj
     * @param  FormRequest  $request
     * @return array
     */
    protected function fill($obj, FormRequest $request): array
    {
        $data = parent::fill($obj, $request);

        if ($this->useGroups) {
            $data['article_group_id'] = $request->get('article_group_id');
        }

        $data['published_at'] = strtotime($request->get('published_at') . ' ' . date('H:i:s'));

        return $data;
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings()
    {
        $result = [
            SiteLimit::make()->setName(__('cms-articles::admin.Site articles limit at page')),
        ];

        if (!$this->useGroups) {
            $result[] = SeoFields::make('Articles');
        }

        $result[] = AdminLimit::make();

        return $result;
    }
}
