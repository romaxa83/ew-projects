@extends('cms-core::admin.crud.index')

@php
    /**
     * @var $result \WezomCms\Cars\Models\Transmission[]
     */
@endphp

@section('content')

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-core::admin.layout.Name')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody data-params="{{ json_encode(['model' => encrypt($model)]) }}">
            @foreach($result as $obj)

                <tr data-id="{{ $obj->id }}">
                    <td>@massCheck($obj)</td>
                    <td>
                        @editResource($obj)
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @editResource($obj, false)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
