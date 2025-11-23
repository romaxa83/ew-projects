@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName, true)</th>
                <th width="1%">#</th>
                <th>@lang('cms-catalog::admin.products.Name')</th>
                <th>@lang('cms-catalog::admin.products.Cost')</th>
                <th>@lang('cms-catalog::admin.products.Created at')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>{{ $obj->name }}</td>
                    <td>@money($obj->cost, true)</td>
                    <td>{{ $obj->created_at->format('d.m.Y H:i') }}</td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @restoreResource($obj)
                            @deleteResource($obj)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
