@extends('cms-core::admin.layouts.main')

@php
    /**
     * @var $obj \WezomCms\Orders\Models\Order
     * @var $provider int
     */
@endphp

@section('main')
    {!! Form::open(['route' => [$routeName . '.store-item', $obj->id], 'id' => 'form']) !!}
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Add item')</strong></h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('order_category_id', __('cms-orders::admin.orders.Category')) !!}
                        <select name="category_id" id="order_category_id" class="form-control js-select2">
                            <option value="">@lang('cms-core::admin.layout.Not set')</option>
                            @foreach($categoriesTree as $key => $category)
                                <option value="{{ $key }}" {{ $key == old('category_id') ? 'selected': null }}
                                        {{ $category['disabled'] ?? false ? 'disabled' : null }}>{!! $category['name'] !!}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('product_id', __('cms-orders::admin.orders.Product')) !!}
                        @include('cms-catalog::admin.products.select', [
                            'class' => 'js-order-product-selector',
                            'url' => route('admin.products.search', [ 'provider_id' => $provider ])
                        ])
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('quantity', __('cms-orders::admin.orders.Quantity')) !!}
                        <div class="input-group">
                            <input type="number" name="quantity" value="0"
                                   placeholder="@lang('cms-orders::admin.orders.Quantity')"
                                   class="form-control js-order-product-quantity">
                            <div class="input-group-append">
                                <span class="input-group-text js-order-product-quantity-unit" data-default="@lang('cms-orders::admin.pieces')">@lang('cms-orders::admin.pieces')</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @widget('admin:form-buttons')
    {!! Form::close() !!}
@endsection
