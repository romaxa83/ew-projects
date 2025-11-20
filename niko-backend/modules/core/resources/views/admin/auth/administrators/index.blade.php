@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th width="1%">#</th>
                <th>@lang('cms-core::admin.administrators.Name')</th>
                <th>@lang('cms-core::admin.administrators.E-mail')</th>
                <th>@lang('cms-core::admin.administrators.Roles')</th>
                @foreach(array_filter(event('administrators.index_th_render')) as $th)
                    {!! $th !!}
                @endforeach
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td><a href="{{ route($routeName . '.edit', $obj->id) }}">{{ $obj->id }}</a></td>
                    <td>{{ $obj->name }}</td>
                    <td>{{ $obj->email }}</td>
                    <td>
                        @if (count($obj->roles))
                            @foreach ($obj->roles as $role)
                                <a href="{{ route('admin.roles.edit', $role->id) }}" target="_blank"
                                   class="d-block">{{ $role->name }}</a>
                            @endforeach
                        @else
                            @lang('cms-core::admin.layout.Not set')
                        @endif
                    </td>
                    @foreach(array_filter(event('administrators.index_td_render', compact('obj'))) as $td)
                        {!! $td !!}
                    @endforeach
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus(['obj' => $obj, 'field' => 'active'])
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
