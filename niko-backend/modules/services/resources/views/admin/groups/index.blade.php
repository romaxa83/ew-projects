@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="sortable-column"></th>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-services::admin.Name')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody class="js-sortable"
                   data-params="{{ json_encode(['model' => encrypt($model), 'page' => $result->currentPage(), 'limit' => $result->perPage()]) }}">
            @foreach($result as $obj)
                <tr data-id="{{ $obj->id }}">
                    <td>
                        <div class="js-sortable-handle sortable-handle">
                            <i class="fa fa-arrows"></i>
                        </div>
                    </td>
                    <td>@massCheck($obj)</td>
                    <td>@editResource($obj)</td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus($obj)
                            @editResource($obj, false)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
