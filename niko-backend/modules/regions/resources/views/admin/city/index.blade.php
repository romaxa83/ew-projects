@extends('cms-core::admin.crud.index')

@php
    /**
     * @var $result \WezomCms\Regions\Models\City[]
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
                <th>@lang('cms-core::admin.layout.Name')</th>
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
                        @editResource($obj)
                    </td>
                    <td>
                        @editResource(['obj'=> $obj->region, 'text' => $obj->region->name, 'ability' => 'regions.edit', 'target' => '_blank'])
                    </td>
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

