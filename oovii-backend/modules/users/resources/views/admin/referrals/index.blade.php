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
                <th>@lang('cms-users::admin.Referrals number')</th>
                <th>@lang('cms-users::admin.referrals.Bonus sum')</th>
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
                    <td>{{ $obj->referrals_count }}</td>
                    <td>{{ $obj->bonus }}</td>
                    <td>
                        {!! $obj->created_at->format(config('cms.core.time.format.created_at.admin_table')) !!}
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
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
