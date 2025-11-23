@php
/**
 * @var $products \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|\WezomCms\Catalog\Models\Product[]
 */
@endphp
<div>
    <div>
        {!! $products->links() !!}
    </div>
    @if($products->hasMorePages())
        <div>
            <button type="button" data-show-more="{{ $products->nextPageUrl() }}">@lang('cms-product-reviews::site.Показати ще')</button>
        </div>
    @endif
</div>
