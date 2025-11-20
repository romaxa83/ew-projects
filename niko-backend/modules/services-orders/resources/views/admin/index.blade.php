@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-services-orders::admin.ID')</th>
                <th>@lang('cms-services-orders::admin.Name')</th>
                <th>@lang('cms-services::admin.Service')</th>
                <th>@lang('cms-dealerships::admin.Dealership')</th>
                <th>@lang('cms-users::admin.car.description')</th>
                <th>@lang('cms-services-orders::admin.Status')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>
                        <a href="{{ route('admin.users.index', ['id' => $obj->user->id]) }}">{{ $obj->user->full_name }}</a>
                    </td>
                    <td>{{ $obj->group->name }} {{ isset($obj->service->name) ? ' ('. $obj->service->name .')' : '' }}</td>
                    <td>
                        <a href="{{ route('admin.dealerships.edit', ['dealership' => $obj->dealership]) }}">{{ $obj->dealership->name_with_brand }}</a>
                    </td>
                    <td>{{ $obj->car->description ?? ''}}</td>
                    <td>{!! $obj->statusForTable() !!}</td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus(['obj' => $obj, 'field' => 'read'])
{{--                            @editResource($obj, true)--}}
{{--                            @deleteResource($obj)--}}
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
