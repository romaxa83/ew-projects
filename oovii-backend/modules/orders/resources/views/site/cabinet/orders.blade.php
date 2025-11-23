@extends('cms-users::site.layouts.cabinet')

@php
    /**
     * @var $orders \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\Order[]|\Illuminate\Pagination\LengthAwarePaginator
     */
@endphp

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        <div>
            <div>
                @widget('cabinet-menu')
            </div>
            @if($orders->isNotEmpty())
                <div class="js-orders-container">
                    @include('cms-orders::site.partials.cabinet-orders-list')
                </div>
                @if($orders->hasMorePages())
                    <div>
                        <button type="button"
                            data-load-more="{{ json_encode(['route' => $orders->nextPageUrl(), 'appendTo' => '.js-orders-container']) }}"
                        >
                            &orarr;
                            <span>@lang('cms-orders::site.Загрузить еще')</span>
                        </button>
                    </div>
                @endif
            @else
                <div>@lang('cms-orders::site.Вы еще ничего не заказывали')</div>
                <a href="{{ route('home') }}">@lang('cms-orders::site.Вернуться на главную')</a>
            @endif
        </div>
    </div>
@endsection
