@extends('cms-ui::layouts.main')

@php
    /**
     * @var $products \Illuminate\Pagination\LengthAwarePaginator|\WezomCms\Catalog\Models\Product[]
     * @var $query string
     * @var $baseUrl string
     */
$emptyResult = $products->isEmpty();
if (!$emptyResult) {
    $countProducts = $products->total();
    $productsText = trans_choice('cms-catalog::site.товар|товара|товаров', $countProducts);
}
@endphp

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        @if($emptyResult)
            @emptyResult(__('cms-catalog::site.По запросу :query Ничего не найдено', ['query' => $query]))
        @else
            <div>@lang('cms-catalog::site.По запросу :query найдено :count :products_text', ['query' => $query, 'count' => $countProducts, 'products_text' => $productsText])</div>

            <div data-catalog-filter data-base="{{ $baseUrl }}">
                @include('cms-catalog::site.partials.filter')
            </div>
            <div>
                <div data-catalog-main>
                    @include('cms-catalog::site.partials.main-catalog')
                </div>
                <div data-pagination>
                    @include('cms-catalog::site.partials.products-pagination')
                </div>
            </div>
        @endif
    </div>
    @widget('catalog:viewed')
@endsection
