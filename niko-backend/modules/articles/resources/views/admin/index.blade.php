@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="sortable-column"></th>
                <th width="1%">#</th>
                <th>@lang('cms-articles::admin.Name')</th>
                <th>@lang('cms-articles::admin.Text')</th>
                <th>@lang('cms-core::admin.layout.Go to the website')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Publication')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody class="js-sortable"
                   data-params="{{ json_encode(['model' => encrypt(\WezomCms\Articles\Models\Article::class), 'page' => $result->currentPage(), 'limit' => $result->perPage()]) }}">
            @foreach($result as $obj)
                <tr data-id="{{ $obj->id }}">
                    <td>
                        <div class="js-sortable-handle sortable-handle">
                            <i class="fa fa-arrows"></i>
                        </div>
                    </td>
                    <td>@massCheck($obj)</td>
                    <td>@editResource($obj)</td>
                    <td>@editResource(['obj' => $obj, 'text' => str_limit(strip_tags($obj->text), 50) ?: null])</td>
                    <td>@gotosite($obj)</td>
                    <td>@statuses(['obj' => $obj, 'request' => WezomCms\Core\Http\Requests\ChangeStatus\LocaledNameWithSlugRequest::class])
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
