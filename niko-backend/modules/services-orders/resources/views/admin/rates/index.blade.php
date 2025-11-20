@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

@section('content')

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
{{--                <th width="1%">@massControl($routeName)</th>--}}
                <th>@lang('cms-services-orders::admin.ID')</th>
                <th>@lang('cms-services-orders::admin.Name')</th>
                <th>@lang('cms-dealerships::admin.Dealership')</th>
                <th>@lang('cms-services-orders::admin.Rating order')</th>
                <th>@lang('cms-services-orders::admin.Rating services')</th>
                <th>@lang('cms-services-orders::admin.Rating comment')</th>
                <th>@lang('cms-services-orders::admin.Date rate')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
{{--                    <td>@massCheck($obj)</td>--}}
                    <td>{{ $obj->id }}</td>
                    <td>
                        <a href="{{ route('admin.users.index', ['id' => $obj->user->id]) }}">{{ $obj->user->full_name }}</a>
                    </td>
                    <td>
                        <a href="{{ route('admin.dealerships.edit', ['dealership' => $obj->dealership]) }}">{{ $obj->dealership->name_with_brand }}</a>
                    </td>
                    <td>{{ $obj->rating_order }}</td>
                    <td>{{ $obj->rating_services }}</td>
                    <td>{{ $obj->rating_comment }}</td>
                    <td>{{ $obj->rate_date }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
