@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th width="1%">ID</th>
                <th>@lang('cms-users::admin.Full name')</th>
                <th>@lang('cms-users::admin.E-mail')</th>
                <th>@lang('cms-users::admin.Phone')</th>
                <th>@lang('cms-core::admin.layout.Created at')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>@editResource(['obj' => $obj, 'text' => $obj->full_name])</td>
                    <td>{{ $obj->email }}</td>
                    <td>
                        @if($obj->phone)
                            <a href="tel:{{ preg_replace('/[^\d\+]/', '', $obj->phone) }}">{{ $obj->phone }}</a>
                        @else
                            <span class="text text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>
                        {!! $obj->created_at->format(config('cms.core.time.format.created_at.admin_table')) !!}
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus(['obj' => $obj, 'field' => 'active'])
{{--                            @can('users.edit', $obj)--}}
{{--                                <a href="{{ route('admin.users.auth', $obj->id) }}" class="btn btn-info"--}}
{{--                                   target="_blank"--}}
{{--                                   data-toggle="tooltip" title="@lang('cms-users::admin.log in as user')"><i--}}
{{--                                            class="fa fa-sign-in"></i></a>--}}
{{--                            @endcan--}}
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
