<?php

namespace WezomCms\ProductReviews\Exports;

use Exception;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use WezomCms\ProductReviews\Repositories\ProductReviewsRepository;

class ProductReviewsExport implements FromCollection,
    WithMapping,
    WithHeadings
{
    public function __construct(
        protected Request $request,
    )
    {}

    /**
     * @throws Exception
     */
    public function collection()
    {
        $repo = resolve(ProductReviewsRepository::class);

        $filter = $this->request->all();
        $filter['parent_id'] = null;

        return $repo->getAll(['product'], false, $filter, false);
    }

    public function map($model): array
    {
        return [
            $model->id,
            $model->name,
            $model->email,
            $model->product_id,
            $model->product->name,
            $model->like ? __('cms-core::admin.layout.Yes') : __('cms-core::admin.layout.No'),
            $model->text,
            $model->created_at->format(config('cms.core.time.format.created_at.import'))
        ];
    }

    public function headings(): array
    {
        return [
            __('cms-core::admin.layout.ID'),
            __('cms-product-reviews::admin.export.User name'),
            __('cms-product-reviews::admin.export.User email'),
            __('cms-product-reviews::admin.export.Product id'),
            __('cms-product-reviews::admin.export.Product name'),
            __('cms-product-reviews::admin.export.Like/Dislike'),
            __('cms-product-reviews::admin.export.Text'),
            __('cms-core::admin.layout.Created at')
        ];
    }
}

