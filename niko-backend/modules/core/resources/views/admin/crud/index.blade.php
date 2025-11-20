@extends('cms-core::admin.layouts.main')

@php
    $btnCreateHide = $btnCreateHide ?? false
@endphp

@section('page-title-buttons')
    @widget('admin:index-buttons',['btnCreateHide' => $btnCreateHide])
@endsection

@if(isset($filterFields))
    @section('filter')
        {!! \WezomCms\Core\Filter\FilterWidget::make($filterFields) !!}
    @endsection
@endif

@section('main')
    <div class="card">
        <div class="card-body p-0">
            @yield('content')
            @if(isset($result) && ((is_object($result) && method_exists($result, 'isEmpty') && $result->isEmpty()) || empty($result)))
                <div class="p-3 text-danger">
                    @if(request()->except('page', 'per_page', 'filter_form', \WezomCms\Core\Contracts\Filter\RestoreFilterInterface::RESET_FILTER_KEY))
                        @lang('cms-core::admin.layout.No items found using filter')
                    @else
                        @lang('cms-core::admin.layout.No items')
                    @endif
                </div>
            @endif
        </div>
    </div>
    @if(isset($result) && $result instanceof \Illuminate\Pagination\AbstractPaginator && $result->isNotEmpty())
        <div class="mt-2 d-flex justify-content-md-center">
            <div class="mw-100">
                <div class="row">
                    @if($result->hasPages())
                        <div class="col-md mb-3 mb-md-0">
                            <div class="scrollable-auto-x">
                                {!! $result->links() !!}
                            </div>
                        </div>
                    @endif
                    <div class="col-md-auto">
                        <div class="form-inline justify-content-center justify-content-md-start">
                            {!! Form::label('per_page', __('cms-core::admin.layout.Display by')) !!}
                            <div class="col-auto">
                                {!! Form::select(
                                    'per_page',
                                    $perPageList,
                                    $result->perPage(),
                                    [
                                        'class' => 'js-select-per-page',
                                        'data-url' => route(Route::currentRouteName(), Route::current()->parameters + request()->except('per_page', 'page'))
                                    ]
                                ) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

