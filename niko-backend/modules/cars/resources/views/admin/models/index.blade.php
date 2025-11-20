@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

@php
    /**
     * @var $result \WezomCms\Cars\Models\Model[]
     */
@endphp

@section('content')

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-core::admin.layout.Name')</th>
                <th>@lang('cms-cars::admin.Brand')</th>
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
                        @editResource(['obj'=> $obj->brand, 'text' => $obj->brand->name, 'ability' => 'car-brands.edit', 'target' => '_blank'])
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus(['obj' => $obj, 'field' => 'for_trade'])
                            @editResource($obj, false)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
