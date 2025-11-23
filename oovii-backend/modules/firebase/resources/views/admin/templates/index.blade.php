@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-core::admin.layout.ID')</th>
                <th>@lang('cms-firebase::admin.template.form.title')</th>
                <th>@lang('cms-firebase::admin.type.title')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>{{ $obj->translation->title }}</td>
                    <td>{{ \WezomCms\Firebase\Models\Template::listType()[$obj->type] }}</td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus(['obj' => $obj, 'field' => 'active'])
                            @editResource($obj, false)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
