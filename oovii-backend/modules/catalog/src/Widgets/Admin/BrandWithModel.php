<?php

namespace WezomCms\Catalog\Widgets\Admin;

use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\Model;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class BrandWithModel extends AbstractWidget
{
    /**
     * View name.
     *
     * @var string
     */
    protected $view = 'cms-catalog::admin.widgets.brand-with-model';

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $brandsEnabled = config('cms.catalog.brands.enabled', false);
        $modelsEnabled = config('cms.catalog.models.enabled', false);

        $data = compact('brandsEnabled', 'modelsEnabled');

        if ($brandsEnabled) {
            $data['brands'] = [];

            $brand = $this->parameter('brand');
            if ($brand && $brand instanceof Brand) {
                $data['brands'][] = [$brand->id => $brand->name];
            }
        }

        if ($modelsEnabled) {
            $data['models'] = [];
            $model = $this->parameter('model');
            if ($model && $model instanceof Model) {
                $data['models'][] = [$model->id => $model->name];
            }
        }

        return $data;
    }
}
