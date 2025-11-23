@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-pages::admin.Name')</th>
                <th>@lang('cms-pages::admin.Text')</th>
                <th>@lang('cms-pages::admin.Created at')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Publication')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>@editResource($obj)</td>
                    <td>
                        @if(strip_tags($obj->text))
                            {!! str_limit(strip_tags($obj->text)) !!}
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>{{ $obj->created_at->format('Y-m-d') }}</td>
                    <td>@statuses(['obj' => $obj, 'request' => WezomCms\Core\Http\Requests\ChangeStatus\LocaledNameWithSlugRequest::class])</td>
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
