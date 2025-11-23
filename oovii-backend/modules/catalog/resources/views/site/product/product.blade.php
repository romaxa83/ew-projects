@extends('cms-ui::layouts.main')

@section('content')
    @include('cms-catalog::site.partials.product.gallery')
    @include('cms-catalog::site.partials.product.info')
    @widget('orders:product-delivery-and-payment-text')
    @include('cms-catalog::site.partials.product.tabs')
    @widget('catalog:buy-with-this-product', compact('product'))
    @widget('catalog:same-products', compact('product'))
    @widget('catalog:viewed', ['title' => __('cms-catalog::site.Вы недавно просматривали')])
@endsection
