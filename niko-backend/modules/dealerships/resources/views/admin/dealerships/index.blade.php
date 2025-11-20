@extends('cms-core::admin.crud.index')

@php
    /**
     * @var $result \WezomCms\Dealerships\Models\Dealership[]
     */
@endphp

@section('content')

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="sortable-column"></th>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-core::admin.layout.Name')</th>
                <th>@lang('cms-dealerships::admin.Average order rate')</th>
                <th>@lang('cms-dealerships::admin.Average services rate')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody class="js-sortable"
                   data-params="{{ json_encode(['model' => encrypt($model)]) }}">
            @foreach($result as $obj)
                <tr data-id="{{ $obj->id }}">
                    <td>
                        <div class="js-sortable-handle sortable-handle">
                            <i class="fa fa-arrows"></i>
                        </div>
                    </td>
                    <td>@massCheck($obj)</td>
                    <td>
                        {{ $obj->name_with_brand }}
                    </td>
                    <td>{!! $obj->renderProgressBarOrderRate() !!}</td>
                    <td>{!! $obj->renderProgressBarServiceRate() !!}</td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus($obj)
                            @editResource($obj, false)
                            @deleteResource($obj)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

