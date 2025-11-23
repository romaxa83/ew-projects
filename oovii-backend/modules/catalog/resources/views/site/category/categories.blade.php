@php
    /**
     * @var $result \Illuminate\Pagination\LengthAwarePaginator|\WezomCms\Catalog\Models\Category[]
     **/
@endphp

@extends('cms-ui::layouts.main')

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        <div>
            @if($result->isNotEmpty())
                <div class="js-categories-container">
                    @include('cms-catalog::site.partials.categories-list')
                </div>

                @if($result->hasMorePages())
                    <div>
                        <button type="button"
                                data-load-more="{{ json_encode(['route' => $result->nextPageUrl(), 'appendTo' => '.js-categories-container']) }}"
                        >
                            &orarr;
                            <span>@lang('cms-catalog::site.Загрузить еще')</span>
                        </button>
                    </div>
                @endif
            @else
                <div>
                    @emptyResult
                </div>
            @endif
        </div>
    </div>

    @widget('catalog:popular')
@endsection

@section('hideH1', true)
