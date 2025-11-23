@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-core::admin.layout.ID')</th>
                <th>@lang('cms-imports::admin.uploader')</th>
                <th>@lang('cms-imports::admin.message')</th>
                <th>@lang('cms-imports::admin.status.title')</th>
                <th>@lang('cms-core::admin.layout.Created at')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>{{ $obj->administrator->name ?? null }}</td>
                    <td>{{ $obj->message}}</td>
                    <td>{!! $obj->render() !!}</td>
                    <td>
                        {!! $obj->created_at->format(config('cms.core.time.format.created_at.admin_table')) !!}
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @deleteResource($obj)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

