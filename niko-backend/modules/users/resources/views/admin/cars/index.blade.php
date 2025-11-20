@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-users::admin.car.vin code')</th>
                <th>@lang('cms-users::admin.car.number')</th>
                <th>@lang('cms-users::admin.car.description')</th>
                <th>@lang('cms-users::admin.Owner')</th>
                <th>@lang('cms-users::admin.car.statuses')</th>
{{--                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>--}}
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->vin_code }}</td>
                    <td>{{ $obj->number }}</td>
                    <td>{{ $obj->description }}</td>
                    <td>
                        <a href="{{ route('admin.users.index', ['id' => $obj->user->id]) }}">{{ $obj->user->full_name }}</a>
                    </td>
                    <td>
                        {!! $obj->statuses !!}
                    </td>
{{--                    <td>--}}
{{--                        <div class="btn-group list-control-buttons" role="group">--}}
{{--                            @editResource($obj, false)--}}
{{--                            @deleteResource($obj)--}}
{{--                        </div>--}}
{{--                    </td>--}}
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection

