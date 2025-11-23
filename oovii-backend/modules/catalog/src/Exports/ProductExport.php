<?php

namespace WezomCms\Catalog\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Catalog\Repositories\SpecificationRepository;

class ProductExport implements FromCollection,
    WithMapping,
    WithHeadings
{

    private $specIds = [];

    public function __construct(
        protected Request $request,
        protected ProductRepository $repo
    )
    {}

    /**
     * @throws \Exception
     */
    public function collection()
    {
        return $this->repo->getAll([
                'translations',
                'collections',
                'publishedSpecifications.translations',
                'labels'
            ],
            false,
            $this->request->all()
        );
    }

    public function map($model): array
    {
        /** @var $model Product */
        $tranRU = $model->translations->where('locale', 'ru')->first();
        $tranKK = $model->translations->where('locale', 'kk')->first();

        $value = [
            $model->id,
            $model->group_key,
            $model->brand_id,
            $model->category_id,
            $tranRU->name,
            $tranRU->description,
            $tranRU->feature_1,
            $tranRU->feature_2,
            $tranRU->feature_3,
            $tranKK->name,
            $tranKK->description,
            $tranKK->feature_1,
            $tranKK->feature_2,
            $tranKK->feature_3,
            $model->published_at ? $model->published_at->format(config('cms.core.time.format.created_at.import')) : null,
            $model->expires_at ? $model->expires_at->format(config('cms.core.time.format.created_at.import')) : null,
            $model->cost,
            $model->cost_discount,
            (int)$model->amount,
            (int)$model->amount_one_user,
            $model->sort,
            $model->provider_id,
            $model->moderator_id,
            $model->collections->isEmpty() ? null : $model->collections->first()->id,
            $model->labels->isEmpty() ? null : $model->labels->pluck('id')->implode(','),
            $model->weight,
            $model->dimensions ? implode('*', $model->dimensions) : null,
        ];

        return array_merge($value, $this->getSpecValues($model));
    }

    public function headings(): array
    {
        $head = [
            __('cms-core::admin.layout.ID'),
            __('cms-catalog::admin.products.Group key'),
            __('cms-catalog::admin.products.Brand') . ' ID',
            __('cms-catalog::admin.products.Category') . ' ID',
            __('cms-catalog::admin.products.Name') . ' ru',
            __('cms-catalog::admin.products.Text') . ' ru',
            __('cms-catalog::admin.products.feature_1') . ' ru',
            __('cms-catalog::admin.products.feature_2') . ' ru',
            __('cms-catalog::admin.products.feature_3') . ' ru',
            __('cms-catalog::admin.products.Name') . ' kz',
            __('cms-catalog::admin.products.Text') . ' kz',
            __('cms-catalog::admin.products.feature_1') . ' kz',
            __('cms-catalog::admin.products.feature_2') . ' kz',
            __('cms-catalog::admin.products.feature_3') . ' kz',
            __('cms-catalog::admin.products.Published at'),
            __('cms-catalog::admin.products.Expires at'),
            __('cms-catalog::admin.products.Cost'),
            __('cms-catalog::admin.products.cost discount'),
            __('cms-catalog::admin.products.amount'),
            __('cms-catalog::admin.products.amount one user'),
            __('cms-catalog::admin.products.Position'),
            __('cms-providers::admin.provider.Provider') . 'ID',
            __('cms-core::admin.moderator.moderator') . 'ID',
            __('cms-catalog::admin.collection.collections') . 'ID',
            __('cms-catalog::admin.labels.names') . ' IDS',
            __('cms-catalog::admin.products.weight'),
            __('cms-catalog::admin.Dimensions'),
        ];

        return array_merge($head, $this->getSpecTitles());
    }

    private function getSpecTitles(): array
    {
        $specs = app(SpecificationRepository::class)->getAll(['translations']);

        $temp = [];
        foreach ($specs as $k => $spec){
            $temp[$k] = $this->getTransTitle($spec);
            $this->specIds[$k] = $spec->id;
        }

        return $temp;
    }

    private function getSpecValues($model): array
    {
        $temp = [];
        foreach ($this->specIds as $key => $id){
            $data = $model->publishedSpecifications->where('specification_id', $id)->first();
            if($data){
                $temp[$key] = $this->getTransTitle($data);
            } else {
                $temp[$key] = null;
            }
        }

        return $temp;
    }

    private function getTransTitle($item): string
    {
        $nameRU = $item->translations->where('locale', 'ru')->first()->name;
        $nameKK = $item->translations->where('locale', 'kk')->first()->name;

        return $nameKK . '/' . $nameRU;
    }
}


