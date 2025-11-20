@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

<?php

//?>

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-users::admin.ID')</th>
                <th>@lang('cms-users::admin.Name')</th>
                <th>@lang('cms-users::admin.E-mail')</th>
                <th>@lang('cms-users::admin.Phone')</th>
                <th>@lang('cms-users::admin.Garage')</th>
                <th>@lang('cms-users::admin.car.statuses')</th>
{{--                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>--}}
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>{{ $obj->full_name }}</td>
                    <td>{{ $obj->email }}</td>
                    <td>{{ $obj->phone }}</td>
                    <td>
                        <a href="{{ route('admin.user-cars.index', ['user' => $obj->id]) }}">Гараж ({{$obj->countCar()}})</a>
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
