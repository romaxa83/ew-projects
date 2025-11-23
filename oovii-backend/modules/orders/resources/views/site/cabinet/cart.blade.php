@extends('cms-users::site.layouts.cabinet')

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        <div>
            <div>
                @widget('cabinet-menu')
            </div>
            <div>
                <div data-cart-display="detail">@lang('cms-orders::site.Загрузка')...</div>
            </div>
        </div>
    </div>
@endsection
