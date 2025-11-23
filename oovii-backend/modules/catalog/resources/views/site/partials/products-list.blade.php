@php
    /**
     * @var $products \Illuminate\Pagination\LengthAwarePaginator|\WezomCms\Catalog\Models\Product[]
     */
@endphp
@foreach($products as $product)
    @include('cms-catalog::site.partials.product-list-item')
@endforeach
