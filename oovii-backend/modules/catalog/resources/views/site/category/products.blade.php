@php
    /**
     * @var $products \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\Product[]
     * @var $category \WezomCms\Catalog\Models\Category
     * @var $selected \Illuminate\Support\Collection
     * @var $sort \WezomCms\Catalog\Filter\Sort
     * @var $searchForm iterable
     * @var $baseUrl string
     */
@endphp

@extends('cms-ui::layouts.main')

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
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
    </div>

    @widget('catalog:viewed')
    @widget('catalog:popular')
@endsection

@section('hideH1', true)
