<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use Illuminate\Http\Request;
use WezomCms\Catalog\Http\Requests\Admin\CatalogSeoTemplateRequest;
use WezomCms\Catalog\Models\CatalogSeoTemplate;
use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Input;
use WezomCms\Core\Settings\MetaFields\Description;
use WezomCms\Core\Settings\MetaFields\Heading;
use WezomCms\Core\Settings\MetaFields\Keywords;
use WezomCms\Core\Settings\MetaFields\Title;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;
use WezomCms\Core\Traits\SettingControllerTrait;

class CatalogSeoTemplatesController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = CatalogSeoTemplate::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-catalog::admin.catalog-seo-templates';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.catalog-seo-templates';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = CatalogSeoTemplateRequest::class;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-catalog::admin.catalog-seo-templates.SEO templates');
    }

    /**
     * @param  CatalogSeoTemplate  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function createViewData($obj, array $viewData): array
    {
        return [
            'categories' => Category::getForSelect(),
            'selectedCategories' => old('CATEGORIES', []),
            'parameters' => CatalogSeoTemplate::availableParameters(),
            'selectedParameters' => [],
        ];
    }

    /**
     * @param  CatalogSeoTemplate  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function editViewData($obj, array $viewData): array
    {
        return [
            'categories' => Category::getForSelect(),
            'selectedCategories' => old('CATEGORIES', $obj->categories()->pluck('id')->toArray()),
            'parameters' => CatalogSeoTemplate::availableParameters(),
            'selectedParameters' => $obj->catalogSeoTemplateParameters()->pluck('parameter')->toArray(),
        ];
    }

    /**
     * @param  CatalogSeoTemplate  $obj
     * @param  Request  $request
     */
    protected function afterSuccessfulStore($obj, Request $request)
    {
        $data = collect($request->get('PARAMETERS'))
            ->map(function ($value) {
                return ['parameter' => $value];
            })
            ->toArray();

        $obj->catalogSeoTemplateParameters()->createMany($data);

        $obj->categories()->sync($request->get('CATEGORIES', []));
    }

    /**
     * @param  CatalogSeoTemplate  $obj
     * @param  Request  $request
     */
    protected function afterSuccessfulUpdate($obj, Request $request)
    {
        $obj->catalogSeoTemplateParameters()->delete();

        $this->afterSuccessfulStore($obj, $request);
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        $result = [];

        $items = [
            Title::make()
                ->setHelpText(__('cms-catalog::admin.catalog-seo-templates.Categories meta-tags keys')),
            Heading::make()
                ->setHelpText(__('cms-catalog::admin.catalog-seo-templates.Categories meta-tags keys')),
            Description::make()
                ->setHelpText(__('cms-catalog::admin.catalog-seo-templates.Categories meta-tags keys')),
            Keywords::make()
                ->setHelpText(__('cms-catalog::admin.catalog-seo-templates.Categories meta-tags keys')),
        ];
        $result[] = new MultilingualGroup(
            new RenderSettings(
                new Tab(
                    'default-template',
                    __('cms-catalog::admin.catalog-seo-templates.Default product template'),
                    1,
                    'fa-folder-o'
                )
            ),
            $items
        );

        // Image templates
        $result[] = new MultilingualGroup(
            new RenderSettings(
                new Tab(
                    'image-template',
                    __('cms-catalog::admin.catalog-seo-templates.Image template'),
                    1,
                    'fa-picture-o'
                )
            ),
            [
                Input::make()
                    ->setIsMultilingual()
                    ->setName(__('cms-catalog::admin.catalog-seo-templates.Image alt'))
                    ->setHelpText(__('cms-catalog::admin.catalog-seo-templates.Image meta-tags keys'))
                    ->setKey('alt'),
                Input::make()
                    ->setIsMultilingual()
                    ->setName(__('cms-catalog::admin.catalog-seo-templates.Image title'))
                    ->setHelpText(__('cms-catalog::admin.catalog-seo-templates.Image meta-tags keys'))
                    ->setKey('title'),
            ]
        );

        return $result;
    }
}
